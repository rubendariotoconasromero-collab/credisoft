<template>
    <div class="modal fade" id="modalRespaldos" tabindex="-1" aria-labelledby="modalRespaldosLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" style="width:90%; max-width:90%">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title text-dark fw-bold" id="modalRespaldosLabel">
                        <i class="fas fa-folder-open me-2"></i>Gestión de Respaldos de Verificación
                    </h5>
                    <button type="button" class="btn-close btn-close-dark" @click="cerrar"
                        aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div v-if="cargando" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>

                    <div v-else>
                        <div v-for="(respaldo, index) in listaRespaldos" :key="index" class="mb-4">
                            <div class="mb-3 d-flex align-items-center">
                                <label class="form-label fw-semibold me-2 col-auto">Descripción Respaldo:</label>
                                <input type="text" class="form-control" v-model="respaldo.descripcion"
                                    :class="{ 'is-invalid': !respaldo.descripcion && intentoGuardar }" 
                                    placeholder="Ej. Foto del negocio, Croquis..." />
                                
                                <button v-if="listaRespaldos.length > 1" @click="eliminarRespaldo(index)"
                                    class="btn btn-danger btn-sm ms-2 rounded-circle" data-bs-toggle="tooltip"
                                    title="Eliminar respaldo">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            
                            <div class="row row-cols-1 row-cols-md-3 g-3">
                                <div v-for="(imagen, index2) in respaldo.lista_imagenes" :key="index2" class="col">
                                    <div class="card shadow-sm h-100">
                                        <div style="height: 150px; overflow: hidden; background: #f8f9fa;">
                                            <img :src="obtenerSrcImagen(imagen)" 
                                                 class="card-img-top h-100 w-100" 
                                                 style="object-fit: cover;"
                                                 alt="Imagen respaldo" />
                                        </div>
                                        
                                        <div class="card-body p-2">
                                            <div class="input-group input-group-sm">
                                                <input type="file" class="form-control" accept="image/*"
                                                    @change="seleccionarImagen($event, index2, index, respaldo)"
                                                    :disabled="imagen.id_imagen !== 0" />
                                                
                                                <button v-if="respaldo.lista_imagenes.length > 1"
                                                    @click="eliminarImagen(index, index2)"
                                                    class="btn btn-secondary ms-1 rounded-circle"
                                                    data-bs-toggle="tooltip" title="Eliminar imagen">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                
                                                <button @click="agregarImagen(index)"
                                                    class="btn btn-success ms-2 rounded-circle" data-bs-toggle="tooltip"
                                                    title="Agregar imagen"
                                                    v-if="index2 + 1 == respaldo.lista_imagenes.length">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr v-if="index < listaRespaldos.length - 1" class="text-muted">
                        </div>

                        <div class="mt-3">
                            <button @click="agregarRespaldo" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Agregar Nuevo Respaldo
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-success" @click="guardarTodo" :disabled="guardando">
                        <i v-if="!guardando" class="fas fa-save me-1"></i>
                        <i v-else class="fas fa-spinner fa-spin me-1"></i>
                        {{ guardando ? 'Guardando...' : 'Guardar Todo' }}
                    </button>
                    <button type="button" class="btn btn-secondary" @click="cerrar">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            idSolicitud: null,
            listaRespaldos: [],
            deletedImages: [],
            deletedRespaldos: [],
            cargando: false,
            guardando: false,
            intentoGuardar: false,
            defaultImage: "img/respaldos/default.png"
        };
    },
    methods: {
        async abrir(idSolicitud) {
            this.idSolicitud = idSolicitud;
            this.cargando = true;
            this.listaRespaldos = [];
            this.deletedImages = [];
            this.deletedRespaldos = [];
            this.intentoGuardar = false;

            $('#modalRespaldos').modal('show');

            try {
                const { data } = await axios.get(`/get_respaldos?id_solicitud=${idSolicitud}`);
                
                if (data.success && data.data.length) {
                    this.listaRespaldos = data.data.map(respaldo => ({
                        id: respaldo.id,
                        id_solicitud: respaldo.id_solicitud,
                        descripcion: respaldo.descripcion,
                        lista_imagenes: respaldo.lista_imagenes.length ?
                            respaldo.lista_imagenes.map(img => ({
                                id_imagen: img.id_imagen,
                                id_respaldo: img.id_respaldo,
                                imagen: img.imagen,
                                imagen_file: null,
                                isNew: false
                            })) : [{ id_imagen: 0, id_respaldo: 0, imagen: '', imagen_file: null, isNew: true }]
                    }));
                } else {
                    // Si no hay datos, iniciamos uno vacío
                    this.listaRespaldos = [{
                        id: 0,
                        id_solicitud: idSolicitud,
                        descripcion: '',
                        lista_imagenes: [{ id_imagen: 0, id_respaldo: 0, imagen: '', imagen_file: null, isNew: true }]
                    }];
                }
            } catch (error) {
                console.error('Error fetching respaldos:', error);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los respaldos' });
            } finally {
                this.cargando = false;
            }
        },

        cerrar() {
            // Limpieza de memoria
            this.listaRespaldos.forEach(respaldo => {
                respaldo.lista_imagenes.forEach(imagen => {
                    if (imagen.imagen && imagen.isNew && imagen.imagen.startsWith('blob:')) {
                        URL.revokeObjectURL(imagen.imagen);
                    }
                });
            });
            this.listaRespaldos = [];
            $('#modalRespaldos').modal('hide');
            this.$emit('cerrar');
        },

        // --- GESTIÓN DE ARRAY LOCAL ---
        
        agregarRespaldo() {
            this.listaRespaldos.push({
                id: 0,
                id_solicitud: this.idSolicitud,
                descripcion: '',
                lista_imagenes: [{ id_imagen: 0, id_respaldo: 0, imagen: '', imagen_file: null, isNew: true }]
            });
        },

        eliminarRespaldo(index) {
            const respaldo = this.listaRespaldos[index];
            if (respaldo.id !== 0) {
                this.deletedRespaldos.push(respaldo.id);
            }
            this.listaRespaldos.splice(index, 1);
        },

        // --- GESTIÓN DE IMÁGENES ---

        obtenerSrcImagen(imagenObj) {
            if (imagenObj.isNew && imagenObj.imagen && imagenObj.imagen.startsWith('blob:')) {
                return imagenObj.imagen;
            }
            return imagenObj.imagen ? `img/respaldos/${imagenObj.imagen}` : this.defaultImage;
        },

        seleccionarImagen(event, index2, index, respaldo) {
            const file = event.target.files[0];
            if (!file) return;

            const tempUrl = URL.createObjectURL(file);
            const imagenActual = this.listaRespaldos[index].lista_imagenes[index2];

            // Reemplazo reactivo
            this.listaRespaldos[index].lista_imagenes.splice(index2, 1, {
                ...imagenActual,
                imagen: tempUrl,
                imagen_file: file,
                isNew: true
            });
        },

        agregarImagen(index) {
            this.listaRespaldos[index].lista_imagenes.push({
                id_imagen: 0,
                id_respaldo: this.listaRespaldos[index].id || 0,
                imagen: '',
                imagen_file: null,
                isNew: true
            });
        },

        eliminarImagen(index, index2) {
            const imagen = this.listaRespaldos[index].lista_imagenes[index2];
            if (imagen.id_imagen !== 0) {
                this.deletedImages.push(imagen.id_imagen);
            }
            this.listaRespaldos[index].lista_imagenes.splice(index2, 1);
        },

        // --- GUARDADO ---

        async guardarTodo() {
            this.intentoGuardar = true;
            
            // Validación
            for (const [idx, respaldo] of this.listaRespaldos.entries()) {
                if (!respaldo.descripcion.trim()) {
                    Swal.fire({ icon: 'error', title: 'Falta Descripción', text: `El respaldo #${idx + 1} no tiene descripción.` });
                    return;
                }
                // Validar que si hay imágenes nuevas, tengan archivo
                for (const img of respaldo.lista_imagenes) {
                    if (img.isNew && !img.imagen_file && !img.imagen) {
                         // Nota: Ajusta validación según si permites respaldos sin foto temporalmente
                    }
                }
            }

            this.guardando = true;
            try {
                const formData = new FormData();
                const respaldosData = [];

                this.listaRespaldos.forEach((respaldo, index) => {
                    const respaldoInfo = {
                        id: respaldo.id || 0,
                        descripcion: respaldo.descripcion,
                        id_solicitud: respaldo.id_solicitud,
                        lista_imagenes: []
                    };

                    respaldo.lista_imagenes.forEach((imagen, imgIndex) => {
                        if (imagen.isNew && imagen.imagen_file) {
                            const fileKey = `imagen_${index}_${imgIndex}`;
                            formData.append(fileKey, imagen.imagen_file);
                            respaldoInfo.lista_imagenes.push({
                                id_imagen: imagen.id_imagen,
                                id_respaldo: imagen.id_respaldo,
                                file_key: fileKey
                            });
                        } else if (!imagen.isNew) {
                            respaldoInfo.lista_imagenes.push({
                                id_imagen: imagen.id_imagen,
                                id_respaldo: imagen.id_respaldo
                            });
                        }
                    });
                    respaldosData.push(respaldoInfo);
                });

                formData.append('respaldos', JSON.stringify(respaldosData));
                formData.append('deletedImages', JSON.stringify(this.deletedImages));
                formData.append('deletedRespaldos', JSON.stringify(this.deletedRespaldos));

                await axios.post('/guardar_respaldos_imagenes', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                Swal.fire({ icon: 'success', title: 'Guardado exitoso', showConfirmButton: false, timer: 1500 });
                this.cerrar();

            } catch (error) {
                console.error('Error guardando respaldos:', error);
                Swal.fire({ icon: 'error', title: 'Error', text: error.response?.data?.message || 'No se pudo guardar.' });
            } finally {
                this.guardando = false;
            }
        }
    }
};
</script>
<style scoped>
@import './../styles/frmSolicitud.css';
</style>