<template>
    <main class="guarantor-management">
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
                                        <input v-model="searchQuery" type="text" class="form-control" @input="buscarCodeudor()" />
                                        <button class="btn btn-success"><i class="fas fa-search"></i></button>
                                        <button class="btn btn-warning ms-1" @click="imprimirReporteCodeudores()"><i class="fas fa-print"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-4 my-1 text-end">
                                    <button @click="openNewGuarantorModal()" class="btn btn-success">
                                        <i class="fas fa-plus-circle"></i> Nuevo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive" style="font-size: 11px">
                            <table class="table table-hover table-striped table-sm table-guarantors">
                                <thead class="table-success text-dark text-uppercase">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>CI</th>
                                        <th>Sexo</th>
                                        <th>E.Civil</th>
                                        <th>Actividad</th>
                                        <th>Vivienda</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in guarantors" :key="item.id" class="align-middle">
                                        <td class="text-uppercase fw-bold">{{ item.nombre }}</td>
                                        <td class="text-uppercase">{{ item.ci }}</td>
                                        <td class="text-uppercase">{{ item.sexo }}</td>
                                        <td class="text-uppercase">{{ item.estado_civil }}</td>
                                        <td class="text-uppercase">{{ item.actividad }}</td>
                                        <td class="text-uppercase">{{ item.vivienda }}</td>
                                        <td class="text-uppercase">{{ item.tipo }}</td>
                                        <td class="text-uppercase">
                                            <span :class="item.estado === 1 ? 'badge bg-success w-100' : 'badge bg-danger w-100'">
                                                {{ item.estado === 1 ? "Activo" : "Inactivo" }}
                                            </span>
                                        </td>
                                        <td class="text-uppercase position-relative text-center">
                                            <div class="dropdown">
                                                <a class="text-success dropdown-toggle" style="cursor: pointer" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-h fa-lg"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                                    <li v-if="item.estado === 1" @click="deactivateGuarantor(item)">
                                                        <a class="dropdown-item text-danger" href="#"><i class="fas fa-times"></i> Desactivar</a>
                                                    </li>
                                                    <li v-else @click="activateGuarantor(item)">
                                                        <a class="dropdown-item text-success" href="#"><i class="fas fa-check"></i> Activar</a>
                                                    </li>
                                                    <li @click="editGuarantor(item)">
                                                        <a class="dropdown-item text-primary" href="#"><i class="fas fa-pencil-alt"></i> Editar</a>
                                                    </li>
                                                    <li @click="viewGuarantor(item)">
                                                        <a class="dropdown-item text-info" href="#"><i class="fas fa-eye"></i> Ver</a>
                                                    </li>
                                                    <li @click="viewGuarantorPdf(item)">
                                                        <a class="dropdown-item text-danger" href="#"><i class="fas fa-file-pdf"></i> PDF</a>
                                                    </li>
                                                    <li @click="openPhotoModal(item)">
                                                        <a class="dropdown-item text-warning" href="#">
                                                            <i class="fas fa-user"></i> {{ item.imagen ? "Ver/Cambiar Foto" : "Agregar Foto" }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <template v-if="guarantors.length <= 7">
                                <br><br><br><br><br><br><br><br><br><br>
                            </template>
                        </div>
                    </div>
                </div>

                <CodeudorForm 
                    v-if="view == 1"
                    :codeudor-id="selectedGuarantorId"
                    :accion="formAction"
                    :initial-data="selectedGuarantorData"
                    @cerrar="closeModal"
                    @guardado="onGuarantorSaved"
                />

                <div id="modalPhotoList" class="modal fade" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-dark border-2">
                            <div class="modal-header bg-warning text-white">
                                <h5 class="modal-title text-white"><i class="fas fa-camera me-2"></i>Foto del Codeudor/Garante</h5>
                                <button type="button" class="btn-close btn-close-white" @click="closePhotoModal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <div class="image-preview-container mb-4">
                                    <img :src="previewImage || (selectedGuarantorData && selectedGuarantorData.imagen ? `/img/codeudor/${selectedGuarantorData.imagen}` : '/img/codeudor/default.png')" class="img-thumbnail customer-photo" alt="Foto" />
                                </div>
                                <div class="file-upload-wrapper">
                                    <label for="listPhotoUpload" class="btn btn-outline-success w-100">
                                        <i class="fas fa-cloud-upload-alt me-2"></i>Seleccionar nueva foto
                                    </label>
                                    <input id="listPhotoUpload" type="file" class="d-none" accept="image/*" @change="previewPhoto" />
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
import axios from "axios";
import Swal from "sweetalert2";
// IMPORTAR EL COMPONENTE HIJO
import CodeudorForm from './Codeudor/CodeudorForm.vue';

export default {
    components: {
        CodeudorForm
    },
    data() {
        return {
            view: 0,
            preloader: false,
            guarantors: [],
            searchCriteria: "codeudor.nombre",
            searchQuery: "",
            debounceTimeout: null,

            // Variables para el componente hijo
            selectedGuarantorId: 0,
            selectedGuarantorData: null,
            formAction: 0, // 0:Nuevo, 1:Editar, 2:Ver

            // Variables Foto (Lista)
            selectedFile: null,
            previewImage: null,
        };
    },
    methods: {
        // --- Navegación y Formulario ---
        openNewGuarantorModal() {
            this.selectedGuarantorId = 0;
            this.selectedGuarantorData = null;
            this.formAction = 0;
            this.view = 1;
        },
        editGuarantor(item) {
            this.selectedGuarantorId = item.id;
            this.selectedGuarantorData = item;
            this.formAction = 1;
            this.view = 1;
        },
        viewGuarantor(item) {
            this.selectedGuarantorId = item.id;
            this.selectedGuarantorData = item;
            this.formAction = 2;
            this.view = 1;
        },
        closeModal() {
            this.view = 0;
            this.selectedGuarantorId = 0;
            this.selectedGuarantorData = null;
        },
        async onGuarantorSaved() {
            await this.fetchGuarantors();
        },

        // --- Listado y Acciones ---
        buscarCodeudor() {
            if (this.debounceTimeout) clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => { this.fetchGuarantors(); }, 300);
        },
        async fetchGuarantors() {
            try {
                const response = await axios.get("/get_codeudores", {
                    params: { criterio: this.searchCriteria, buscar: this.searchQuery },
                });
                this.guarantors = response.data;
            } catch (error) { console.error("Error fetching guarantors:", error); }
        },
        async activateGuarantor(item) {
            try {
                await axios.get(`/activar_codeudor?id_codeudor=${item.id}`);
                await this.fetchGuarantors();
            } catch (error) { console.error(error); }
        },
        async deactivateGuarantor(item) {
            try {
                await axios.get(`/desactivar_codeudor?id_codeudor=${item.id}`);
                await this.fetchGuarantors();
            } catch (error) { console.error(error); }
        },
        
        // --- Reportes y PDF ---
        
        imprimirReporteCodeudores() {
            Swal.fire({
                title: 'Generando Reporte',
                text: 'Por favor espere...',
                icon: 'info',
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            axios.get('/codeudor/reporte', { params: { buscar: this.searchQuery, criterio: this.searchCriteria }, responseType: 'blob' })
                .then((response) => {
                    const file = new Blob([response.data], { type: 'application/pdf' });
                    const url = window.URL.createObjectURL(file);
                    window.open(url, '_blank');
                    Swal.close();
                }).catch(() => {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo generar el reporte.' });
                });
        },
        
        async viewGuarantorPdf(item) {
            this.preloader = true;
            try {
                const response = await axios.get("/codeudores_pdf", { params: { id_codeudor: item.id }, responseType: "blob" });
                const blob = new Blob([response.data], { type: "application/pdf" });
                window.open(window.URL.createObjectURL(blob), "_blank");
            } catch (error) { Swal.fire("Error", "No se pudo generar el PDF", "error"); } 
            finally { this.preloader = false; }
        },

        // --- Foto Rápida (Desde Lista) ---
        openPhotoModal(item) {
            this.selectedGuarantorData = item;
            $("#modalPhotoList").modal("show");
        },
        closePhotoModal() {
            $("#modalPhotoList").modal("hide");
            this.previewImage = null; this.selectedFile = null;
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
            formData.append("id_codeudor", this.selectedGuarantorData.id);
            formData.append("imagen", this.selectedFile);
            try {
                await axios.post("/fotoCodeudor", formData);
                Swal.fire({ icon: "success", title: "Foto actualizada", timer: 1500 });
                this.closePhotoModal();
                await this.fetchGuarantors();
            } catch (error) { Swal.fire("Error", "No se pudo actualizar", "error"); }
        }
    },
    async mounted() {
        this.preloader = true;
        await this.fetchGuarantors();
        this.preloader = false;
    }
};
</script>

<style scoped>
@import './styles/frmCliente.css';
/* Estilos extra para el modal de foto en lista si es necesario */
.customer-photo { width: 200px; height: 200px; object-fit: cover; border-radius: 50%; border: 3px solid #198754; }
</style>