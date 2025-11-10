<!-- Modal Cambiar Foto de Perfil -->
<div class="modal fade" id="modalCambiarFoto" tabindex="-1" aria-labelledby="modalCambiarFotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCambiarFotoLabel">
                    <i class="ti ti-camera me-2"></i>
                    Cambiar Foto de Perfil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCambiarFoto" enctype="multipart/form-data">
                    <!-- Vista Previa -->
                    <div class="preview-container" id="preview-container" style="display: none;">
                        <img id="preview-image" src="" alt="Vista Previa" class="preview-image">
                    </div>
                    
                    <!-- Texto cuando no hay preview -->
                    <div class="text-center text-muted mb-3" id="no-preview-text">
                        <i class="ti ti-photo" style="font-size: 4rem; color: #d0d0d0;"></i>
                        <p class="mb-0">Vista previa de tu nueva foto</p>
                    </div>
                    
                    <!-- Input de archivo personalizado -->
                    <div class="file-upload-wrapper mb-3">
                        <label for="file-upload-input" class="file-upload-label">
                            <i class="ti ti-upload"></i>
                            <span id="file-upload-label-text">Seleccionar Imagen</span>
                            <small class="d-block mt-2 text-muted">Haz clic para elegir una foto</small>
                        </label>
                        <input type="file" 
                               id="file-upload-input" 
                               name="foto_perfil" 
                               accept="image/jpeg,image/jpg,image/png,image/gif" 
                               required>
                    </div>
                    
                    <!-- Información sobre requisitos -->
                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="ti ti-info-circle me-1"></i>
                            <strong>Requisitos de la imagen:</strong>
                            <ul class="mb-0 mt-2" style="padding-left: 1.2rem;">
                                <li>Formatos permitidos: JPG, PNG, GIF</li>
                                <li>Tamaño máximo: 5 MB</li>
                                <li>Recomendado: Imagen cuadrada (300x300 px o superior)</li>
                            </ul>
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>
                    Cancelar
                </button>
                <button type="submit" form="formCambiarFoto" class="btn btn-primary-custom">
                    <i class="ti ti-check me-2"></i>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos del modal */
#modalCambiarFoto .modal-content {
    border-radius: 14px;
    border: none;
    overflow: hidden;
}

#modalCambiarFoto .modal-header {
    background: linear-gradient(135deg, #B4E7CE 0%, #D4F1E8 100%);
    color: white;
    border-radius: 14px 14px 0 0;
    border: none;
    padding: 1.25rem 1.5rem;
}

#modalCambiarFoto .modal-header .btn-close {
    filter: brightness(0) invert(1);
}

#modalCambiarFoto .modal-body {
    padding: 2rem 1.5rem;
}

#modalCambiarFoto .modal-footer {
    border-top: 1px solid #f0f0f0;
    padding: 1rem 1.5rem;
}

/* Preview de imagen */
.preview-container {
    text-align: center;
    margin: 0 0 1.5rem 0;
    padding: 1rem;
    background: linear-gradient(135deg, #E8F5E9 0%, #F1F8E9 100%);
    border-radius: 12px;
}

.preview-image {
    max-width: 250px;
    max-height: 250px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #B4E7CE;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Wrapper del input de archivo */
.file-upload-wrapper {
    position: relative;
    display: block;
    width: 100%;
}

.file-upload-label {
    display: block;
    width: 100%;
    padding: 1.5rem 1rem;
    background: linear-gradient(135deg, #E8F5E9 0%, #F1F8E9 100%);
    border: 2px dashed #B4E7CE;
    border-radius: 12px;
    cursor: pointer;
    text-align: center;
    transition: all 0.3s ease;
    color: #2c3e50;
    font-weight: 600;
    margin: 0;
}

.file-upload-label:hover {
    background: linear-gradient(135deg, #D4F1E8 0%, #E8F5E9 100%);
    border-color: #A0D7BB;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(180, 231, 206, 0.3);
}

.file-upload-label i {
    font-size: 2.5rem;
    color: #B4E7CE;
    display: block;
    margin-bottom: 0.75rem;
}

.file-upload-label small {
    font-weight: 400;
    font-size: 0.85rem;
}

#file-upload-input {
    display: none;
}

/* Botón personalizado */
.btn-primary-custom {
    background: linear-gradient(135deg, #B4E7CE 0%, #D4F1E8 100%);
    color: white;
    border: none;
    padding: 0.65rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary-custom:hover {
    background: linear-gradient(135deg, #A0D7BB 0%, #C0E7D5 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(180, 231, 206, 0.4);
}

.btn-secondary {
    padding: 0.65rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
}

/* Alert info personalizado */
#modalCambiarFoto .alert-info {
    background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
    border: 1px solid #64B5F6;
    border-radius: 10px;
    color: #1565C0;
}

#modalCambiarFoto .alert-info ul {
    margin-bottom: 0;
}

#modalCambiarFoto .alert-info li {
    font-size: 0.85rem;
    line-height: 1.6;
}

/* Responsive */
@media (max-width: 576px) {
    #modalCambiarFoto .modal-dialog {
        margin: 0.5rem;
    }
    
    #modalCambiarFoto .modal-body {
        padding: 1.5rem 1rem;
    }
    
    .preview-image {
        max-width: 200px;
        max-height: 200px;
    }
    
    .file-upload-label {
        padding: 1.25rem 0.75rem;
    }
    
    .file-upload-label i {
        font-size: 2rem;
    }
}

/* Animación de entrada */
#modalCambiarFoto.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

#modalCambiarFoto.show .modal-dialog {
    transform: none;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    
    // Preview de imagen al seleccionar archivo
    $('#file-upload-input').on('change', function(e) {
        const file = e.target.files[0];
        
        if (file) {
            // Validar tipo de archivo
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tipo de archivo no válido',
                    text: 'Por favor selecciona una imagen (JPG, PNG o GIF)',
                    confirmButtonColor: '#B4E7CE',
                    confirmButtonText: 'Entendido'
                });
                $(this).val('');
                return;
            }
            
            // Validar tamaño (máximo 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB en bytes
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'Archivo muy grande',
                    text: 'La imagen no debe superar los 5MB',
                    confirmButtonColor: '#B4E7CE',
                    confirmButtonText: 'Entendido'
                });
                $(this).val('');
                return;
            }
            
            // Mostrar preview
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-image').attr('src', e.target.result);
                $('#preview-container').fadeIn(300);
                $('#no-preview-text').fadeOut(300);
            };
            reader.readAsDataURL(file);
            
            // Actualizar texto del label con el nombre del archivo
            const fileName = file.name.length > 30 ? file.name.substring(0, 27) + '...' : file.name;
            $('#file-upload-label-text').text(fileName);
        }
    });

    // Guardar nueva foto de perfil
    $('#formCambiarFoto').on('submit', function(e) {
        e.preventDefault();
        
        const fileInput = $('#file-upload-input')[0];
        if (!fileInput.files || fileInput.files.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor selecciona una imagen',
                confirmButtonColor: '#B4E7CE',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        const formData = new FormData(this);
        formData.append('accion', 'cambiar_foto_perfil');
        formData.append('usuario_id', usuario_id);
        
        // Mostrar loading
        Swal.fire({
            title: 'Subiendo imagen...',
            html: 'Por favor espera mientras se procesa la imagen',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: 'modales/mi_perfil/procesar_mi_perfil.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.mensaje,
                        confirmButtonColor: '#B4E7CE',
                        confirmButtonText: 'Entendido'
                    }).then(() => {
                        // Actualizar imagen en la página con timestamp para evitar caché
                        const timestamp = new Date().getTime();
                        $('#profileAvatarImg').attr('src', response.nueva_foto + '?t=' + timestamp);
                        
                        // Actualizar también en el header si existe
                        if ($('#headerProfileImg').length) {
                            $('#headerProfileImg').attr('src', response.nueva_foto + '?t=' + timestamp);
                        }
                        
                        // Cerrar modal
                        $('#modalCambiarFoto').modal('hide');
                        
                        // Resetear formulario
                        resetearFormularioFoto();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje,
                        confirmButtonColor: '#B4E7CE',
                        confirmButtonText: 'Entendido'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
                console.error('Response:', xhr.responseText);
                
                let mensajeError = 'Ocurrió un error al procesar la solicitud';
                
                // Intentar obtener mensaje de error del servidor
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.mensaje) {
                        mensajeError = response.mensaje;
                    }
                } catch (e) {
                    // Si no se puede parsear, usar mensaje genérico
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: mensajeError,
                    confirmButtonColor: '#B4E7CE',
                    confirmButtonText: 'Entendido'
                });
            }
        });
    });
    
    // Resetear modal al cerrar
    $('#modalCambiarFoto').on('hidden.bs.modal', function () {
        resetearFormularioFoto();
    });
    
    // Función para resetear el formulario
    function resetearFormularioFoto() {
        $('#formCambiarFoto')[0].reset();
        $('#preview-container').hide();
        $('#no-preview-text').show();
        $('#file-upload-label-text').text('Seleccionar Imagen');
        $('#preview-image').attr('src', '');
    }
    
    // Prevenir que el modal se cierre al hacer clic en el backdrop durante la subida
    let uploadingPhoto = false;
    
    $('#formCambiarFoto').on('submit', function() {
        uploadingPhoto = true;
    });
    
    $(document).ajaxComplete(function() {
        uploadingPhoto = false;
    });
    
    $('#modalCambiarFoto').on('hide.bs.modal', function(e) {
        if (uploadingPhoto) {
            e.preventDefault();
            Swal.fire({
                icon: 'info',
                title: 'Por favor espera',
                text: 'La imagen se está subiendo, no cierres esta ventana',
                confirmButtonColor: '#B4E7CE',
                confirmButtonText: 'Entendido'
            });
        }
    });
    
});
</script>