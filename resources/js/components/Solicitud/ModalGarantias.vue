<template>
    <div class="modal fade" id="modalGarantias" tabindex="-1" aria-labelledby="modalGarantiasLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" style="width:90%; max-width:90%">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title text-dark fw-bold" id="modalGarantiasLabel">
                        <i class="fas fa-images me-2"></i>Gestión de Garantías
                    </h5>
                    <button type="button" class="btn-close btn-close-white" @click="cerrar"
                        aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div v-if="cargando" class="text-center py-5">
                        <div class="spinner-border text-warning" role="status"></div>
                    </div>

                    <div v-else>
                        <div v-for="(item, index) in listaGarantias" :key="index" class="mb-4">
                            <div class="mb-3 d-flex align-items-center">
                                <label class="form-label fw-semibold me-2 col-auto">Descripción Garantía:</label>
                                <input type="text" class="form-control" v-model="item.descripcion" />
                            </div>
                            
                            <div class="row row-cols-1 row-cols-md-3 g-3">
                                <div v-for="(imagen, index2) in item.lista_imagenes" :key="index2" class="col">
                                    <div class="card shadow-sm h-100">
                                        <div style="height: 150px; overflow: hidden; background: #f8f9fa;">
                                            <img :src="obtenerSrcImagen(imagen)" 
                                                 class="card-img-top h-100 w-100" 
                                                 style="object-fit: cover;"
                                                 alt="Imagen garantía" />
                                        </div>
                                        
                                        <div class="card-body p-2">
                                            <div class="input-group input-group-sm">
                                                <input type="file" class="form-control" accept="image/*"
                                                    @change="seleccionarImagen($event, index2, index, item)"
                                                    :disabled="imagen.id_imagen !== 0" />
                                                
                                                <button v-if="item.lista_imagenes.length > 1"
                                                    @click="eliminarImagen(index, index2)"
                                                    class="btn btn-secondary ms-1 rounded-circle"
                                                    data-bs-toggle="tooltip" title="Eliminar imagen">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                
                                                <button @click="agregarImagen(index)"
                                                    class="btn btn-success ms-2 rounded-circle" data-bs-toggle="tooltip"
                                                    title="Agregar imagen"
                                                    v-if="index2 + 1 == item.lista_imagenes.length">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr v-if="index < listaGarantias.length - 1" class="text-muted">
                        </div>
                        
                        <div v-if="listaGarantias.length === 0" class="text-center text-muted py-4">
                            No se encontraron garantías registradas para esta solicitud.
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
            cargando: false,
            guardando: false,
            listaGarantias: [],
            deletedImages: [],
            deletedGarantias: [],
            defaultImage: "img/respaldos/default.png", // Ajusta la ruta si es necesario
        };
    },
    methods: {
        // --- MÉTODOS DE APERTURA/CIERRE ---
        
        async abrir(idSolicitud) {
            this.cargando = true;
            this.listaGarantias = [];
            this.deletedImages = [];
            this.deletedGarantias = [];
            
            $('#modalGarantias').modal('show');

            try {
                // 1. Obtener las garantías base
                const response = await axios.get(`/get_garantias?id_solicitud=${idSolicitud}`);
                const garantiasBase = response.data.garantias;

                if (!garantiasBase.length) {
                    this.listaGarantias = [];
                    this.cargando = false;
                    return;
                }

                // 2. Preparar estructura y cargar imágenes para CADA garantía
                // Usamos Promise.all para cargar las imágenes de todas las garantías en paralelo
                const promesasImagenes = garantiasBase.map(async (garantia) => {
                    try {
                        const resImg = await axios.get(`/get_imagenes_garantia?id_garantia=${garantia.id}`);
                        const imagenes = resImg.data.length
                            ? resImg.data.map(img => ({
                                id_imagen: img.id,
                                id_garantia: img.id_garantia,
                                imagen: img.imagen,
                                imagen_file: null,
                                isNew: false
                            }))
                            : [{ id_imagen: 0, id_garantia: 0, imagen: "", imagen_file: null, isNew: true }];
                        
                        return {
                            ...garantia,
                            id_solicitud: idSolicitud, // Aseguramos tener el ID
                            lista_imagenes: imagenes
                        };
                    } catch (err) {
                        console.error(`Error cargando imágenes garantía ${garantia.id}`, err);
                        return { ...garantia, id_solicitud: idSolicitud, lista_imagenes: [] };
                    }
                });

                this.listaGarantias = await Promise.all(promesasImagenes);

            } catch (error) {
                console.error("Error fetching garantias:", error);
                Swal.fire({ icon: "error", title: "Error", text: "No se pudieron cargar las garantías" });
            } finally {
                this.cargando = false;
            }
        },

        cerrar() {
            // Limpiar URLs temporales para liberar memoria
            this.listaGarantias.forEach(garantia => {
                garantia.lista_imagenes.forEach(imagen => {
                    if (imagen.imagen && imagen.isNew && imagen.imagen.startsWith('blob:')) {
                        URL.revokeObjectURL(imagen.imagen);
                    }
                });
            });
            
            this.listaGarantias = [];
            this.deletedImages = [];
            this.deletedGarantias = [];
            
            $('#modalGarantias').modal('hide');
            this.$emit('cerrar');
        },

        // --- MÉTODOS DE IMAGEN ---

        obtenerSrcImagen(imagenObj) {
            if (imagenObj.isNew && imagenObj.imagen && imagenObj.imagen.startsWith('blob:')) {
                return imagenObj.imagen; // Blob local
            }
            return imagenObj.imagen ? `img/garantia/${imagenObj.imagen}` : this.defaultImage;
        },

        seleccionarImagen(event, index2, index, item) {
            const file = event.target.files[0];
            if (!file) return;

            const originalImage = { ...this.listaGarantias[index].lista_imagenes[index2] };
            
            // Liberar memoria si ya había un blob previo
            if (originalImage.imagen && originalImage.imagen.startsWith('blob:')) {
                URL.revokeObjectURL(originalImage.imagen);
            }
            
            const tempUrl = URL.createObjectURL(file);

            // Reemplazar en el array reactivo
            this.listaGarantias[index].lista_imagenes.splice(index2, 1, {
                id_imagen: originalImage.id_imagen || 0,
                id_garantia: item.id || 0,
                imagen: tempUrl,
                imagen_file: file,
                isNew: true // Marcamos como nuevo o editado
            });
        },

        agregarImagen(index) {
            this.listaGarantias[index].lista_imagenes.push({
                id_imagen: 0,
                id_garantia: this.listaGarantias[index].id || 0,
                imagen: '',
                imagen_file: null,
                isNew: true,
            });
        },

        eliminarImagen(index, index2) {
            const imagen = this.listaGarantias[index].lista_imagenes[index2];
            // Si tiene ID real (viene de BD), lo agregamos a la lista de borrados
            if (imagen.id_imagen !== 0) {
                this.deletedImages.push(imagen.id_imagen);
            }
            // Lo quitamos de la vista
            this.listaGarantias[index].lista_imagenes.splice(index2, 1);
        },

        // --- GUARDADO ---

        async guardarTodo() {
            // Validaciones
            for (const [index, garantia] of this.listaGarantias.entries()) {
                for (const [imgIndex, imagen] of garantia.lista_imagenes.entries()) {
                    if (imagen.isNew && !imagen.imagen_file && !imagen.imagen) {
                        // Nota: !imagen.imagen checkea si está vacío el string
                        Swal.fire({
                            icon: 'error',
                            title: 'Falta Imagen',
                            text: `Por favor, carga una imagen para el elemento ${imgIndex + 1} en la garantía: "${garantia.descripcion}".`,
                        });
                        return;
                    }
                }
            }

            this.guardando = true;
            try {
                const formData = new FormData();
                const garantiasData = [];

                this.listaGarantias.forEach((garantia, index) => {
                    const garantiaData = {
                        id: garantia.id || 0,
                        descripcion: garantia.descripcion,
                        id_solicitud: garantia.id_solicitud,
                        lista_imagenes: [],
                    };

                    garantia.lista_imagenes.forEach((imagen, imgIndex) => {
                        // Caso 1: Imagen Nueva (tiene archivo)
                        if (imagen.isNew && imagen.imagen_file) {
                            const fileKey = `imagen_${index}_${imgIndex}`;
                            formData.append(fileKey, imagen.imagen_file);
                            garantiaData.lista_imagenes.push({
                                id_imagen: imagen.id_imagen,
                                id_garantia: imagen.id_garantia,
                                file_key: fileKey,
                            });
                        } 
                        // Caso 2: Imagen Existente (no es nueva)
                        else if (!imagen.isNew) {
                            garantiaData.lista_imagenes.push({
                                id_imagen: imagen.id_imagen,
                                id_garantia: imagen.id_garantia,
                            });
                        }
                    });

                    garantiasData.push(garantiaData);
                });

                // Adjuntar JSONs
                formData.append('garantias', JSON.stringify(garantiasData));
                formData.append('deletedImages', JSON.stringify(this.deletedImages));
                formData.append('deletedGarantias', JSON.stringify(this.deletedGarantias));

                await axios.post('/guardar_garantias_imagenes', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Guardado exitoso',
                    showConfirmButton: false,
                    timer: 1500,
                });
                this.cerrar();

            } catch (error) {
                console.error('Error saving garantias:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo guardar las garantías',
                });
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