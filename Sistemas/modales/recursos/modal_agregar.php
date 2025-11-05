<!-- Modal Agregar Recurso -->
<div class="modal fade" id="modalAgregarRecurso" tabindex="-1" aria-labelledby="modalAgregarRecursoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalAgregarRecursoLabel">
                    <i class="ti ti-file-plus me-2"></i>
                    Nuevo Recurso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAgregarRecurso" method="POST" enctype="multipart/form-data">
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
                                            <label for="add_titulo" class="form-label">
                                                Título del Recurso <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_titulo" name="titulo" 
                                                   placeholder="Título descriptivo del recurso" required 
                                                   maxlength="255" minlength="5">
                                            <div class="form-text">Mínimo 5 caracteres, máximo 255</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_tipo" class="form-label">
                                                Tipo de Recurso <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_tipo" name="tipo" required>
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
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="add_descripcion" class="form-label">
                                                Descripción
                                            </label>
                                            <textarea class="form-control" id="add_descripcion" name="descripcion" 
                                                      rows="3" placeholder="Descripción detallada del recurso" 
                                                      maxlength="500"></textarea>
                                            <div class="form-text">Máximo 500 caracteres</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Archivo o Enlace -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-upload me-2"></i>
                                        Archivo o Enlace
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- Sección para Archivo -->
                                    <div id="seccion_archivo">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label for="add_archivo" class="form-label">
                                                    Seleccionar Archivo <span class="text-danger">*</span>
                                                </label>
                                                <input type="file" class="form-control" id="add_archivo" name="archivo">
                                                <div class="form-text" id="archivo_help_text">
                                                    Formatos permitidos según tipo seleccionado. Máximo 50MB.
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="alert alert-info mb-0">
                                                    <strong>Formatos permitidos por tipo:</strong>
                                                    <ul class="mb-0 mt-2">
                                                        <li><strong>Video:</strong> MP4, AVI, MOV, WMV (máx. 50MB)</li>
                                                        <li><strong>PDF:</strong> PDF (máx. 20MB)</li>
                                                        <li><strong>Imagen:</strong> JPG, PNG, GIF, SVG (máx. 5MB)</li>
                                                        <li><strong>Audio:</strong> MP3, WAV, OGG (máx. 20MB)</li>
                                                        <li><strong>Documento:</strong> DOC, DOCX, TXT, RTF (máx. 10MB)</li>
                                                        <li><strong>Presentación:</strong> PPT, PPTX (máx. 20MB)</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sección para Enlace -->
                                    <div id="seccion_enlace" style="display: none;">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label for="add_url" class="form-label">
                                                    URL del Recurso <span class="text-danger">*</span>
                                                </label>
                                                <input type="url" class="form-control" id="add_url" name="url" 
                                                       placeholder="https://ejemplo.com/recurso" maxlength="500">
                                                <div class="form-text">URL completa del recurso externo</div>
                                            </div>
                                            <div class="col-12">
                                                <div class="alert alert-warning mb-0">
                                                    <i class="ti ti-alert-triangle me-2"></i>
                                                    <strong>Nota:</strong> Asegúrese de que el enlace sea público y accesible.
                                                </div>
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
                                                <input class="form-check-input" type="checkbox" id="add_publico" 
                                                       name="publico" value="1" checked>
                                                <label class="form-check-label" for="add_publico">
                                                    Recurso Público
                                                </label>
                                            </div>
                                            <div class="form-text">Si está desactivado, solo tú podrás verlo</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Descarga</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="add_descargable" 
                                                       name="descargable" value="1" checked>
                                                <label class="form-check-label" for="add_descargable">
                                                    Permitir Descarga
                                                </label>
                                            </div>
                                            <div class="form-text">Permite que estudiantes descarguen el archivo</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_duracion" class="form-label">
                                                Duración (para videos/audios)
                                            </label>
                                            <input type="text" class="form-control" id="add_duracion" name="duracion" 
                                                   placeholder="HH:MM:SS" pattern="^([0-9]{2}):([0-5][0-9]):([0-5][0-9])$">
                                            <div class="form-text">Formato: 00:05:30 (opcional)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Etiquetas y Categorización -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-tags me-2"></i>
                                        Etiquetas y Categorización
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label for="add_etiquetas" class="form-label">
                                                Etiquetas (separadas por comas)
                                            </label>
                                            <input type="text" class="form-control" id="add_etiquetas" name="etiquetas" 
                                                   placeholder="matemática, geometría, secundaria">
                                            <div class="form-text">Máximo 10 etiquetas, cada una de 2-20 caracteres</div>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarRecurso">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Recurso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.campo-error {
    background-color: #fff5f5;
    border: 2px solid #dc3545 !important;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 20%, 40%, 60%, 80% { transform: translateX(0); }
    10%, 30%, 50%, 70% { transform: translateX(-5px); }
}

#preview_recurso {
    max-width: 100%;
    max-height: 200px;
    margin-top: 10px;
    border-radius: 8px;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Cambiar entre archivo y enlace según tipo
    $('#add_tipo').on('change', function() {
        const tipo = $(this).val();
        
        if (tipo === 'ENLACE') {
            $('#seccion_archivo').hide();
            $('#seccion_enlace').show();
            $('#add_archivo').prop('required', false);
            $('#add_url').prop('required', true);
        } else {
            $('#seccion_archivo').show();
            $('#seccion_enlace').hide();
            $('#add_archivo').prop('required', true);
            $('#add_url').prop('required', false);
        }
        
        // Actualizar ayuda de formatos
        actualizarAyudaFormatos(tipo);
    });

    // Validar archivo según tipo
    $('#add_archivo').on('change', function() {
        const archivo = this.files[0];
        if (!archivo) return;

        const tipo = $('#add_tipo').val();
        if (!tipo) {
            mostrarErrorValidacion('Primero selecciona el tipo de recurso', '#add_tipo');
            $(this).val('');
            return;
        }

        // Validar según tipo
        const validacion = validarArchivoSegunTipo(archivo, tipo);
        if (!validacion.valido) {
            mostrarErrorValidacion(validacion.mensaje, '#add_archivo');
            $(this).val('');
            return;
        }

        // Preview para imágenes
        if (tipo === 'IMAGEN') {
            mostrarPreviewImagen(archivo);
        }
    });

    // Validar URL
    $('#add_url').on('blur', function() {
        const url = $(this).val().trim();
        if (url && !validarURL(url)) {
            mostrarErrorValidacion('URL no válida. Debe comenzar con http:// o https://', '#add_url');
        }
    });

    // Validar duración
    $('#add_duracion').on('blur', function() {
        const duracion = $(this).val().trim();
        if (duracion && !validarDuracion(duracion)) {
            mostrarErrorValidacion('Formato inválido. Use HH:MM:SS (ej: 00:05:30)', '#add_duracion');
        }
    });

    // Validar etiquetas
    $('#add_etiquetas').on('blur', function() {
        const etiquetas = $(this).val().trim();
        if (etiquetas && !validarEtiquetas(etiquetas)) {
            mostrarErrorValidacion('Máximo 10 etiquetas, cada una de 2-20 caracteres', '#add_etiquetas');
        }
    });

    // Envío del formulario
    $('#formAgregarRecurso').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioCompletoRecurso()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        mostrarCarga();
        $('#btnGuardarRecurso').prop('disabled', true);

        $.ajax({
            url: 'modales/recursos/procesar_recursos.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarRecurso').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Recurso Creado!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAgregarRecurso').modal('hide');
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
                $('#btnGuardarRecurso').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar
    $('#modalAgregarRecurso').on('hidden.bs.modal', function() {
        limpiarFormularioRecurso();
    });
});

// VALIDACIÓN COMPLETA DEL FORMULARIO (25 VALIDACIONES)
function validarFormularioCompletoRecurso() {
    let isValid = true;
    let erroresEncontrados = [];
    
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // 1. Validar título (obligatorio, 5-255 caracteres)
    const titulo = $('#add_titulo').val().trim();
    if (!titulo) {
        marcarCampoError('#add_titulo', 'El título es obligatorio');
        erroresEncontrados.push('Título requerido');
        isValid = false;
    } else if (titulo.length < 5 || titulo.length > 255) {
        marcarCampoError('#add_titulo', 'El título debe tener entre 5 y 255 caracteres');
        erroresEncontrados.push('Título: longitud incorrecta (5-255 caracteres)');
        isValid = false;
    }
    
    // 2. Validar tipo de recurso
    const tipo = $('#add_tipo').val();
    if (!tipo) {
        marcarCampoError('#add_tipo', 'Debe seleccionar un tipo de recurso');
        erroresEncontrados.push('Tipo de recurso requerido');
        isValid = false;
    }
    
    // 3-4. Validar descripción (opcional, pero si existe debe ser válida)
    const descripcion = $('#add_descripcion').val().trim();
    if (descripcion && descripcion.length > 500) {
        marcarCampoError('#add_descripcion', 'La descripción no puede superar los 500 caracteres');
        erroresEncontrados.push('Descripción: muy larga (máximo 500 caracteres)');
        isValid = false;
    }
    
    // 5-15. Validar archivo o enlace según tipo
    if (tipo === 'ENLACE') {
        const url = $('#add_url').val().trim();
        if (!url) {
            marcarCampoError('#add_url', 'La URL es obligatoria para enlaces');
            erroresEncontrados.push('URL requerida');
            isValid = false;
        } else if (!validarURL(url)) {
            marcarCampoError('#add_url', 'URL no válida');
            erroresEncontrados.push('URL: formato inválido');
            isValid = false;
        } else if (url.length > 500) {
            marcarCampoError('#add_url', 'La URL no puede superar los 500 caracteres');
            erroresEncontrados.push('URL: muy larga');
            isValid = false;
        }
    } else {
        const archivo = $('#add_archivo')[0].files[0];
        if (!archivo) {
            marcarCampoError('#add_archivo', 'Debe seleccionar un archivo');
            erroresEncontrados.push('Archivo requerido');
            isValid = false;
        } else {
            const validacion = validarArchivoSegunTipo(archivo, tipo);
            if (!validacion.valido) {
                marcarCampoError('#add_archivo', validacion.mensaje);
                erroresEncontrados.push('Archivo: ' + validacion.mensaje);
                isValid = false;
            }
        }
    }
    
    // 16-17. Validar duración (opcional, pero si existe debe ser válida)
    const duracion = $('#add_duracion').val().trim();
    if (duracion && !validarDuracion(duracion)) {
        marcarCampoError('#add_duracion', 'Formato de duración inválido (use HH:MM:SS)');
        erroresEncontrados.push('Duración: formato inválido');
        isValid = false;
    }
    
    // 18-20. Validar etiquetas (opcional, pero si existen deben ser válidas)
    const etiquetas = $('#add_etiquetas').val().trim();
    if (etiquetas && !validarEtiquetas(etiquetas)) {
        marcarCampoError('#add_etiquetas', 'Etiquetas inválidas (máx 10, cada una 2-20 chars)');
        erroresEncontrados.push('Etiquetas: formato inválido');
        isValid = false;
    }
    
    // 21. Validar que el título no contenga solo números
    if (titulo && /^\d+$/.test(titulo)) {
        marcarCampoError('#add_titulo', 'El título no puede contener solo números');
        erroresEncontrados.push('Título: no puede ser solo números');
        isValid = false;
    }
    
    // 22. Validar que el título no contenga caracteres especiales peligrosos
    if (titulo && /[<>{}[\]\\]/.test(titulo)) {
        marcarCampoError('#add_titulo', 'El título contiene caracteres no permitidos');
        erroresEncontrados.push('Título: caracteres peligrosos detectados');
        isValid = false;
    }
    
    // 23. Validar coherencia entre tipo y duración
    if (duracion && tipo !== 'VIDEO' && tipo !== 'AUDIO') {
        marcarCampoError('#add_duracion', 'Solo videos y audios pueden tener duración');
        erroresEncontrados.push('Duración: solo para videos/audios');
        isValid = false;
    }
    
    // 24. Validar que si es descargable, no sea enlace
    const descargable = $('#add_descargable').is(':checked');
    if (descargable && tipo === 'ENLACE') {
        $('#add_descargable').prop('checked', false);
        erroresEncontrados.push('Enlaces no pueden ser descargables');
    }
    
    // 25. Validar nombre de archivo si existe
    if (tipo !== 'ENLACE') {
        const archivo = $('#add_archivo')[0].files[0];
        if (archivo && archivo.name.length > 255) {
            marcarCampoError('#add_archivo', 'El nombre del archivo es muy largo');
            erroresEncontrados.push('Nombre de archivo muy largo');
            isValid = false;
        }
    }
    
    if (!isValid) {
        const mensajeError = `❌ NO SE PUEDE CREAR EL RECURSO\n\nErrores encontrados:\n\n• ${erroresEncontrados.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
        
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

function validarArchivoSegunTipo(archivo, tipo) {
    const nombre = archivo.name.toLowerCase();
    const tamano = archivo.size;
    
    const validaciones = {
        'VIDEO': {
            extensiones: ['.mp4', '.avi', '.mov', '.wmv'],
            tamanoMax: 50 * 1024 * 1024, // 50MB
            mensaje: 'Solo se permiten videos MP4, AVI, MOV, WMV (máx. 50MB)'
        },
        'PDF': {
            extensiones: ['.pdf'],
            tamanoMax: 20 * 1024 * 1024, // 20MB
            mensaje: 'Solo se permiten archivos PDF (máx. 20MB)'
        },
        'IMAGEN': {
            extensiones: ['.jpg', '.jpeg', '.png', '.gif', '.svg'],
            tamanoMax: 5 * 1024 * 1024, // 5MB
            mensaje: 'Solo se permiten imágenes JPG, PNG, GIF, SVG (máx. 5MB)'
        },
        'AUDIO': {
            extensiones: ['.mp3', '.wav', '.ogg'],
            tamanoMax: 20 * 1024 * 1024, // 20MB
            mensaje: 'Solo se permiten audios MP3, WAV, OGG (máx. 20MB)'
        },
        'DOCUMENTO': {
            extensiones: ['.doc', '.docx', '.txt', '.rtf'],
            tamanoMax: 10 * 1024 * 1024, // 10MB
            mensaje: 'Solo se permiten documentos DOC, DOCX, TXT, RTF (máx. 10MB)'
        },
        'PRESENTACION': {
            extensiones: ['.ppt', '.pptx'],
            tamanoMax: 20 * 1024 * 1024, // 20MB
            mensaje: 'Solo se permiten presentaciones PPT, PPTX (máx. 20MB)'
        },
        'OTRO': {
            extensiones: [],
            tamanoMax: 20 * 1024 * 1024, // 20MB
            mensaje: 'Archivo no puede superar 20MB'
        }
    };
    
    const validacion = validaciones[tipo];
    if (!validacion) {
        return { valido: false, mensaje: 'Tipo de recurso no válido' };
    }
    
    // Validar extensión
    if (validacion.extensiones.length > 0) {
        const extensionValida = validacion.extensiones.some(ext => nombre.endsWith(ext));
        if (!extensionValida) {
            return { valido: false, mensaje: validacion.mensaje };
        }
    }
    
    // Validar tamaño
    if (tamano > validacion.tamanoMax) {
        const tamanoMaxMB = validacion.tamanoMax / (1024 * 1024);
        return { valido: false, mensaje: `El archivo supera el tamaño máximo de ${tamanoMaxMB}MB` };
    }
    
    return { valido: true, mensaje: '' };
}

function validarURL(url) {
    try {
        const urlObj = new URL(url);
        return urlObj.protocol === 'http:' || urlObj.protocol === 'https:';
    } catch {
        return false;
    }
}

function validarDuracion(duracion) {
    return /^([0-9]{2}):([0-5][0-9]):([0-5][0-9])$/.test(duracion);
}

function validarEtiquetas(etiquetas) {
    const etiquetasArray = etiquetas.split(',').map(e => e.trim()).filter(e => e);
    
    if (etiquetasArray.length > 10) {
        return false;
    }
    
    for (const etiqueta of etiquetasArray) {
        if (etiqueta.length < 2 || etiqueta.length > 20) {
            return false;
        }
    }
    
    return true;
}

function mostrarPreviewImagen(archivo) {
    const reader = new FileReader();
    reader.onload = function(e) {
        let preview = $('#preview_recurso');
        if (preview.length === 0) {
            $('#add_archivo').after('<img id="preview_recurso" alt="Preview">');
            preview = $('#preview_recurso');
        }
        preview.attr('src', e.target.result);
    };
    reader.readAsDataURL(archivo);
}

function actualizarAyudaFormatos(tipo) {
    const mensajes = {
        'VIDEO': 'Formatos: MP4, AVI, MOV, WMV. Máximo 50MB.',
        'PDF': 'Formato: PDF. Máximo 20MB.',
        'IMAGEN': 'Formatos: JPG, PNG, GIF, SVG. Máximo 5MB.',
        'AUDIO': 'Formatos: MP3, WAV, OGG. Máximo 20MB.',
        'DOCUMENTO': 'Formatos: DOC, DOCX, TXT, RTF. Máximo 10MB.',
        'PRESENTACION': 'Formatos: PPT, PPTX. Máximo 20MB.',
        'OTRO': 'Cualquier formato. Máximo 20MB.'
    };
    
    $('#archivo_help_text').text(mensajes[tipo] || mensajes['OTRO']);
}

function marcarCampoError(selector, mensaje) {
    const campo = $(selector);
    campo.addClass('is-invalid campo-error');
    campo.after(`<div class="invalid-feedback">${mensaje}</div>`);
}

function mostrarErrorValidacion(mensaje, selector) {
    $(selector).addClass('campo-error');
    Swal.fire({
        title: 'Error de Validación',
        text: mensaje,
        icon: 'error',
        confirmButtonColor: '#dc3545',
        timer: 3000,
        timerProgressBar: true
    });
}

function limpiarFormularioRecurso() {
    $('#formAgregarRecurso')[0].reset();
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    $('#preview_recurso').remove();
    $('#seccion_archivo').show();
    $('#seccion_enlace').hide();
    $('#add_archivo').prop('required', true);
    $('#add_url').prop('required', false);
}
</script>