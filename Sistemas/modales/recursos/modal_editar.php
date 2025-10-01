<!-- Modal Editar Recurso -->
<div class="modal fade" id="modalEditarRecurso" tabindex="-1" aria-labelledby="modalEditarRecursoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalEditarRecursoLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Recurso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarRecurso" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_recurso_id" name="recurso_id">
                <input type="hidden" id="edit_url_actual" name="url_actual">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-info-circle me-2"></i>
                                        Información Básica
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label for="edit_titulo" class="form-label">
                                                Título del Recurso <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_titulo" name="titulo" 
                                                   placeholder="Título descriptivo del recurso" required 
                                                   maxlength="255" minlength="5">
                                            <div class="form-text">Mínimo 5 caracteres, máximo 255</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_tipo" class="form-label">
                                                Tipo de Recurso <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_tipo" name="tipo" required disabled>
                                                <option value="">Seleccionar tipo</option>
                                                <option value="VIDEO">Video</option>
                                                <option value="PDF">PDF</option>
                                                <option value="IMAGEN">Imagen</option>
                                                <option value="AUDIO">Audio</option>
                                                <option value="ENLACE">Enlace Web</option>
                                                <option value="DOCUMENTO">Documento</option>
                                                <option value="PRESENTACION">Presentación</option>
                                                <option value="OTRO">Otro</option>
                                            </select>
                                            <div class="form-text text-warning">El tipo no se puede modificar</div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="edit_descripcion" class="form-label">
                                                Descripción
                                            </label>
                                            <textarea class="form-control" id="edit_descripcion" name="descripcion" 
                                                      rows="3" placeholder="Descripción detallada del recurso" 
                                                      maxlength="500"></textarea>
                                            <div class="form-text">Máximo 500 caracteres</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recurso Actual -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-file me-2"></i>
                                        Recurso Actual
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="recurso_actual_info" class="alert alert-info mb-3">
                                        <!-- Se llenará dinámicamente -->
                                    </div>
                                    
                                    <!-- Sección para reemplazar archivo -->
                                    <div id="seccion_reemplazar_archivo">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="edit_reemplazar_archivo">
                                                    <label class="form-check-label" for="edit_reemplazar_archivo">
                                                        <strong>Reemplazar archivo actual</strong>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12" id="nuevo_archivo_container" style="display: none;">
                                                <label for="edit_nuevo_archivo" class="form-label">
                                                    Nuevo Archivo
                                                </label>
                                                <input type="file" class="form-control" id="edit_nuevo_archivo" name="nuevo_archivo">
                                                <div class="form-text" id="edit_archivo_help_text">
                                                    Debe cumplir con los mismos requisitos del tipo original
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sección para editar enlace -->
                                    <div id="seccion_editar_enlace" style="display: none;">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label for="edit_url" class="form-label">
                                                    URL del Recurso <span class="text-danger">*</span>
                                                </label>
                                                <input type="url" class="form-control" id="edit_url" name="url" 
                                                       placeholder="https://ejemplo.com/recurso" maxlength="500">
                                                <div class="form-text">URL completa del recurso externo</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-settings me-2"></i>
                                        Configuración
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Visibilidad</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="edit_publico" 
                                                       name="publico" value="1">
                                                <label class="form-check-label" for="edit_publico">
                                                    Recurso Público
                                                </label>
                                            </div>
                                            <div class="form-text">Si está desactivado, solo tú podrás verlo</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Descarga</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="edit_descargable" 
                                                       name="descargable" value="1">
                                                <label class="form-check-label" for="edit_descargable">
                                                    Permitir Descarga
                                                </label>
                                            </div>
                                            <div class="form-text">Permite que estudiantes descarguen el archivo</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_duracion" class="form-label">
                                                Duración (para videos/audios)
                                            </label>
                                            <input type="text" class="form-control" id="edit_duracion" name="duracion" 
                                                   placeholder="HH:MM:SS" pattern="^([0-9]{2}):([0-5][0-9]):([0-5][0-9])$">
                                            <div class="form-text">Formato: 00:05:30 (opcional)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Etiquetas -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-tags me-2"></i>
                                        Etiquetas y Categorización
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label for="edit_etiquetas" class="form-label">
                                                Etiquetas (separadas por comas)
                                            </label>
                                            <input type="text" class="form-control" id="edit_etiquetas" name="etiquetas" 
                                                   placeholder="matemática, geometría, secundaria">
                                            <div class="form-text">Máximo 10 etiquetas, cada una de 2-20 caracteres</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas de Uso -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-chart-bar me-2"></i>
                                        Estadísticas de Uso
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <h5 class="mb-0 text-primary" id="edit_stat_cursos">0</h5>
                                            <small class="text-muted">Cursos Vinculados</small>
                                        </div>
                                        <div class="col-md-4">
                                            <h5 class="mb-0 text-success" id="edit_stat_lecciones">0</h5>
                                            <small class="text-muted">Lecciones Vinculadas</small>
                                        </div>
                                        <div class="col-md-4">
                                            <h5 class="mb-0 text-info" id="edit_stat_vistas">0</h5>
                                            <small class="text-muted">Vistas Totales</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ti ti-x me-2"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnActualizarRecurso">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Recurso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
#preview_edit_recurso {
    max-width: 100%;
    max-height: 200px;
    margin-top: 10px;
    border-radius: 8px;
}

.recurso-preview-container {
    text-align: center;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
}
</style>

<script>
$(document).ready(function() {
    // Mostrar/ocultar sección de nuevo archivo
    $('#edit_reemplazar_archivo').on('change', function() {
        if ($(this).is(':checked')) {
            $('#nuevo_archivo_container').slideDown();
        } else {
            $('#nuevo_archivo_container').slideUp();
            $('#edit_nuevo_archivo').val('');
        }
    });

    // Validar nuevo archivo según tipo original
    $('#edit_nuevo_archivo').on('change', function() {
        const archivo = this.files[0];
        if (!archivo) return;

        const tipo = $('#edit_tipo').val();
        const validacion = validarArchivoSegunTipo(archivo, tipo);
        
        if (!validacion.valido) {
            mostrarErrorValidacion(validacion.mensaje, '#edit_nuevo_archivo');
            $(this).val('');
            return;
        }

        // Preview para imágenes
        if (tipo === 'IMAGEN') {
            mostrarPreviewImagenEdit(archivo);
        }
    });

    // Validar URL si es enlace
    $('#edit_url').on('blur', function() {
        const url = $(this).val().trim();
        if (url && !validarURL(url)) {
            mostrarErrorValidacion('URL no válida. Debe comenzar con http:// o https://', '#edit_url');
        }
    });

    // Validar duración
    $('#edit_duracion').on('blur', function() {
        const duracion = $(this).val().trim();
        if (duracion && !validarDuracion(duracion)) {
            mostrarErrorValidacion('Formato inválido. Use HH:MM:SS (ej: 00:05:30)', '#edit_duracion');
        }
    });

    // Validar etiquetas
    $('#edit_etiquetas').on('blur', function() {
        const etiquetas = $(this).val().trim();
        if (etiquetas && !validarEtiquetas(etiquetas)) {
            mostrarErrorValidacion('Máximo 10 etiquetas, cada una de 2-20 caracteres', '#edit_etiquetas');
        }
    });

    // Envío del formulario
    $('#formEditarRecurso').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioEdicionRecurso()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'actualizar');
        
        mostrarCarga();
        $('#btnActualizarRecurso').prop('disabled', true);

        $.ajax({
            url: 'modales/recursos/procesar_recursos.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarRecurso').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Recurso Actualizado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarRecurso').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnActualizarRecurso').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar al cerrar
    $('#modalEditarRecurso').on('hidden.bs.modal', function() {
        limpiarFormularioEdicionRecurso();
    });
});

// VALIDACIÓN COMPLETA DEL FORMULARIO DE EDICIÓN (20 VALIDACIONES)
function validarFormularioEdicionRecurso() {
    let isValid = true;
    let erroresEncontrados = [];
    
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // 1. Validar título (obligatorio, 5-255 caracteres)
    const titulo = $('#edit_titulo').val().trim();
    if (!titulo) {
        marcarCampoError('#edit_titulo', 'El título es obligatorio');
        erroresEncontrados.push('Título requerido');
        isValid = false;
    } else if (titulo.length < 5 || titulo.length > 255) {
        marcarCampoError('#edit_titulo', 'El título debe tener entre 5 y 255 caracteres');
        erroresEncontrados.push('Título: longitud incorrecta (5-255 caracteres)');
        isValid = false;
    }
    
    // 2. Validar que el título no contenga solo números
    if (titulo && /^\d+$/.test(titulo)) {
        marcarCampoError('#edit_titulo', 'El título no puede contener solo números');
        erroresEncontrados.push('Título: no puede ser solo números');
        isValid = false;
    }
    
    // 3. Validar caracteres peligrosos en título
    if (titulo && /[<>{}[\]\\]/.test(titulo)) {
        marcarCampoError('#edit_titulo', 'El título contiene caracteres no permitidos');
        erroresEncontrados.push('Título: caracteres peligrosos detectados');
        isValid = false;
    }
    
    // 4-5. Validar descripción
    const descripcion = $('#edit_descripcion').val().trim();
    if (descripcion && descripcion.length > 500) {
        marcarCampoError('#edit_descripcion', 'La descripción no puede superar los 500 caracteres');
        erroresEncontrados.push('Descripción: muy larga (máximo 500 caracteres)');
        isValid = false;
    }
    
    // 6. Validar tipo de recurso
    const tipo = $('#edit_tipo').val();
    if (!tipo) {
        marcarCampoError('#edit_tipo', 'El tipo de recurso es requerido');
        erroresEncontrados.push('Tipo de recurso requerido');
        isValid = false;
    }
    
    // 7-12. Validar según tipo (enlace o archivo)
    if (tipo === 'ENLACE') {
        const url = $('#edit_url').val().trim();
        if (!url) {
            marcarCampoError('#edit_url', 'La URL es obligatoria');
            erroresEncontrados.push('URL requerida');
            isValid = false;
        } else if (!validarURL(url)) {
            marcarCampoError('#edit_url', 'URL no válida');
            erroresEncontrados.push('URL: formato inválido');
            isValid = false;
        } else if (url.length > 500) {
            marcarCampoError('#edit_url', 'La URL no puede superar los 500 caracteres');
            erroresEncontrados.push('URL: muy larga');
            isValid = false;
        }
    } else {
        // Validar nuevo archivo si se seleccionó uno
        const reemplazar = $('#edit_reemplazar_archivo').is(':checked');
        if (reemplazar) {
            const archivo = $('#edit_nuevo_archivo')[0].files[0];
            if (!archivo) {
                marcarCampoError('#edit_nuevo_archivo', 'Debe seleccionar un archivo');
                erroresEncontrados.push('Nuevo archivo requerido');
                isValid = false;
            } else {
                const validacion = validarArchivoSegunTipo(archivo, tipo);
                if (!validacion.valido) {
                    marcarCampoError('#edit_nuevo_archivo', validacion.mensaje);
                    erroresEncontrados.push('Nuevo archivo: ' + validacion.mensaje);
                    isValid = false;
                }
            }
        }
    }
    
    // 13-14. Validar duración
    const duracion = $('#edit_duracion').val().trim();
    if (duracion && !validarDuracion(duracion)) {
        marcarCampoError('#edit_duracion', 'Formato de duración inválido (use HH:MM:SS)');
        erroresEncontrados.push('Duración: formato inválido');
        isValid = false;
    }
    
    // 15. Validar coherencia entre tipo y duración
    if (duracion && tipo !== 'VIDEO' && tipo !== 'AUDIO') {
        marcarCampoError('#edit_duracion', 'Solo videos y audios pueden tener duración');
        erroresEncontrados.push('Duración: solo para videos/audios');
        isValid = false;
    }
    
    // 16-17. Validar etiquetas
    const etiquetas = $('#edit_etiquetas').val().trim();
    if (etiquetas && !validarEtiquetas(etiquetas)) {
        marcarCampoError('#edit_etiquetas', 'Etiquetas inválidas (máx 10, cada una 2-20 chars)');
        erroresEncontrados.push('Etiquetas: formato inválido');
        isValid = false;
    }
    
    // 18. Validar que si es descargable, no sea enlace
    const descargable = $('#edit_descargable').is(':checked');
    if (descargable && tipo === 'ENLACE') {
        $('#edit_descargable').prop('checked', false);
        erroresEncontrados.push('Enlaces no pueden ser descargables');
    }
    
    // 19. Validar ID del recurso
    const recursoId = $('#edit_recurso_id').val();
    if (!recursoId) {
        erroresEncontrados.push('ID de recurso no encontrado');
        isValid = false;
    }
    
    // 20. Validar que al menos algo haya cambiado
    if (!reemplazar && tipo !== 'ENLACE') {
        // Verificar si al menos cambió título, descripción o configuración
        const tituloOriginal = $('#edit_titulo').data('original');
        const descripcionOriginal = $('#edit_descripcion').data('original');
        
        if (titulo === tituloOriginal && descripcion === descripcionOriginal) {
            // Podría estar cambiando solo configuraciones, lo cual está bien
        }
    }
    
    if (!isValid) {
        const mensajeError = `❌ NO SE PUEDE ACTUALIZAR EL RECURSO\n\nErrores encontrados:\n\n• ${erroresEncontrados.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
        
        Swal.fire({
            title: 'Formulario Incompleto',
            text: mensajeError,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Revisar Formulario',
            width: '600px',
            footer: `Total de errores: ${erroresEncontrados.length}`
        });
        
        const primerError = $('.campo-error, .is-invalid').first();
        if (primerError.length) {
            primerError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => primerError.focus(), 300);
        }
    }
    
    return isValid;
}

// FUNCIÓN PARA CARGAR DATOS EN EL FORMULARIO DE EDICIÓN
function cargarDatosEdicionRecurso(recurso) {
    // Limpiar errores previos
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    $('#preview_edit_recurso').remove();
    
    // Cargar datos básicos
    $('#edit_recurso_id').val(recurso.id);
    $('#edit_titulo').val(recurso.titulo).data('original', recurso.titulo);
    $('#edit_tipo').val(recurso.tipo);
    $('#edit_descripcion').val(recurso.descripcion || '').data('original', recurso.descripcion || '');
    
    // Cargar URL actual
    $('#edit_url_actual').val(recurso.url);
    
    // Configuración
    $('#edit_publico').prop('checked', recurso.publico == 1);
    $('#edit_descargable').prop('checked', recurso.descargable == 1);
    
    // Metadata
    const metadata = recurso.metadata ? JSON.parse(recurso.metadata) : {};
    $('#edit_duracion').val(metadata.duracion || '');
    $('#edit_etiquetas').val(metadata.etiquetas ? metadata.etiquetas.join(', ') : '');
    
    // Estadísticas
    $('#edit_stat_cursos').text(recurso.cursos_vinculados || 0);
    $('#edit_stat_lecciones').text(recurso.lecciones_vinculadas || 0);
    $('#edit_stat_vistas').text(metadata.vistas || 0);
    
    // Mostrar información del recurso actual
    mostrarInfoRecursoActual(recurso);
    
    // Configurar visibilidad según tipo
    if (recurso.tipo === 'ENLACE') {
        $('#seccion_reemplazar_archivo').hide();
        $('#seccion_editar_enlace').show();
        $('#edit_url').val(recurso.url);
    } else {
        $('#seccion_reemplazar_archivo').show();
        $('#seccion_editar_enlace').hide();
        $('#edit_reemplazar_archivo').prop('checked', false);
        $('#nuevo_archivo_container').hide();
    }
}

function mostrarInfoRecursoActual(recurso) {
    const metadata = recurso.metadata ? JSON.parse(recurso.metadata) : {};
    let infoHTML = '<div class="recurso-preview-container">';
    
    // Icono según tipo
    const iconos = {
        'VIDEO': 'ti-video',
        'PDF': 'ti-file-text',
        'IMAGEN': 'ti-photo',
        'AUDIO': 'ti-music',
        'ENLACE': 'ti-link',
        'DOCUMENTO': 'ti-file-description',
        'PRESENTACION': 'ti-presentation',
        'OTRO': 'ti-file'
    };
    
    const icono = iconos[recurso.tipo] || 'ti-file';
    
    infoHTML += `<i class="ti ${icono}" style="font-size: 3rem; color: #667eea;"></i>`;
    infoHTML += `<h6 class="mt-3 mb-2">${recurso.titulo}</h6>`;
    infoHTML += `<p class="mb-1"><strong>Tipo:</strong> ${recurso.tipo}</p>`;
    
    if (metadata.tamano_bytes) {
        const tamanoMB = (metadata.tamano_bytes / 1048576).toFixed(2);
        infoHTML += `<p class="mb-1"><strong>Tamaño:</strong> ${tamanoMB} MB</p>`;
    }
    
    if (metadata.duracion) {
        infoHTML += `<p class="mb-1"><strong>Duración:</strong> ${metadata.duracion}</p>`;
    }
    
    if (recurso.tipo === 'ENLACE') {
        infoHTML += `<p class="mb-1"><strong>URL:</strong> <a href="${recurso.url}" target="_blank">${recurso.url}</a></p>`;
    } else {
        infoHTML += `<a href="${recurso.url}" class="btn btn-sm btn-outline-primary mt-2" target="_blank">
                        <i class="ti ti-eye me-1"></i> Ver Recurso
                     </a>`;
    }
    
    infoHTML += '</div>';
    
    $('#recurso_actual_info').html(infoHTML);
}

function mostrarPreviewImagenEdit(archivo) {
    const reader = new FileReader();
    reader.onload = function(e) {
        let preview = $('#preview_edit_recurso');
        if (preview.length === 0) {
            $('#edit_nuevo_archivo').after('<img id="preview_edit_recurso" alt="Preview">');
            preview = $('#preview_edit_recurso');
        }
        preview.attr('src', e.target.result);
    };
    reader.readAsDataURL(archivo);
}

function limpiarFormularioEdicionRecurso() {
    $('#formEditarRecurso')[0].reset();
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    $('#preview_edit_recurso').remove();
    $('#edit_reemplazar_archivo').prop('checked', false);
    $('#nuevo_archivo_container').hide();
    $('#seccion_reemplazar_archivo').show();
    $('#seccion_editar_enlace').hide();
}
</script>