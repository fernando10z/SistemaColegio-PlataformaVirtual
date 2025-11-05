<!-- Modal Editar Lección -->
<div class="modal fade" id="modalEditarLeccion" tabindex="-1" aria-labelledby="modalEditarLeccionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalEditarLeccionLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Lección
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarLeccion" method="POST">
                <input type="hidden" id="edit_leccion_id" name="leccion_id">
                
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
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_unidad_id" class="form-label">
                                                Unidad <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_unidad_id" name="unidad_id" required>
                                                <option value="">Seleccionar unidad</option>
                                                <?php foreach ($unidades as $unidad): ?>
                                                    <option value="<?= $unidad['id'] ?>">
                                                        <?= htmlspecialchars($unidad['curso_nombre']) ?> › <?= htmlspecialchars($unidad['titulo']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_titulo" class="form-label">
                                                Título de la Lección <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_titulo" name="titulo" 
                                                   placeholder="Ej: Introducción a los Números Enteros" required 
                                                   maxlength="255" minlength="5">
                                            <div class="form-text">Mínimo 5 caracteres, máximo 255</div>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="edit_orden" class="form-label">
                                                Orden <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="edit_orden" name="orden" 
                                                   required min="1" max="100">
                                            <div class="form-text">1-100</div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="edit_descripcion" class="form-label">
                                                Descripción <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control" id="edit_descripcion" name="descripcion" 
                                                      rows="3" placeholder="Descripción breve de la lección" 
                                                      required minlength="10" maxlength="500"></textarea>
                                            <div class="form-text">Entre 10 y 500 caracteres. <span id="edit_desc_count">0/500</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración de Lección -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-settings me-2"></i>
                                        Configuración de Lección
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_tipo" class="form-label">
                                                Tipo de Lección <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_tipo" name="tipo" required>
                                                <option value="">Seleccionar tipo</option>
                                                <option value="CONTENIDO">Contenido Teórico</option>
                                                <option value="ACTIVIDAD">Actividad Práctica</option>
                                                <option value="EVALUACION">Evaluación</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_tiempo_estimado" class="form-label">
                                                Tiempo Estimado (min) <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="edit_tiempo_estimado" 
                                                   name="tiempo_estimado" required min="1" max="300">
                                            <div class="form-text">1-300 minutos</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="edit_estado" class="form-label">
                                                Estado <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_estado" name="estado" required>
                                                <option value="BORRADOR">Borrador</option>
                                                <option value="PUBLICADO">Publicado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label d-block">Opciones</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="edit_obligatorio" 
                                                       name="obligatorio" value="1">
                                                <label class="form-check-label" for="edit_obligatorio">
                                                    Lección Obligatoria
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contenido de la Lección -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-file-text me-2"></i>
                                        Contenido de la Lección
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="edit_contenido" class="form-label">
                                            Contenido HTML
                                        </label>
                                        <textarea class="form-control" id="edit_contenido" name="contenido" 
                                                  rows="12"></textarea>
                                        <div class="form-text">
                                            Puede usar HTML básico. Tags permitidos: h2, h3, p, strong, em, ul, ol, li, br
                                            <br><span id="edit_contenido_count">0 caracteres</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recursos Adicionales -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-paperclip me-2"></i>
                                        Recursos Adicionales (Opcional)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="editRecursosContainer"></div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="agregarRecursoEdit()">
                                        <i class="ti ti-plus me-2"></i>Agregar Recurso
                                    </button>
                                    <div class="form-text mt-2">
                                        Los recursos son opcionales. Puede agregar hasta 10 recursos por lección.
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
                    <button type="submit" class="btn btn-primary" id="btnActualizarLeccion">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Lección
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let editRecursoIndex = 0;

$(document).ready(function() {
    // Contador de caracteres para descripción
    $('#edit_descripcion').on('input', function() {
        const count = $(this).val().length;
        $('#edit_desc_count').text(`${count}/500`);
        if (count > 500) {
            $(this).addClass('campo-error');
        } else {
            $(this).removeClass('campo-error');
        }
    });

    // Contador de caracteres para contenido
    $('#edit_contenido').on('input', function() {
        const count = $(this).val().length;
        $('#edit_contenido_count').text(`${count} caracteres`);
    });

    // Validación de URLs en tiempo real
    $(document).on('input', '.recurso-url-edit', function() {
        const url = $(this).val();
        if (url && !isValidURL(url)) {
            $(this).addClass('campo-error');
        } else {
            $(this).removeClass('campo-error');
        }
    });

    // Envío del formulario con validaciones
    $('#formEditarLeccion').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioEditarLeccion()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'actualizar');
        
        // Procesar recursos
        const recursos = procesarRecursosEdit();
        formData.set('recursos', JSON.stringify(recursos));
        
        mostrarCarga();
        $('#btnActualizarLeccion').prop('disabled', true);

        $.ajax({
            url: 'modales/lecciones/procesar_lecciones.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarLeccion').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Lección Actualizada!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarLeccion').modal('hide');
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
                $('#btnActualizarLeccion').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });
});

// FUNCIÓN DE VALIDACIÓN COMPLETA (REUTILIZA LA LÓGICA DE AGREGAR)
function validarFormularioEditarLeccion() {
    let isValid = true;
    let errores = [];
    
    // Limpiar errores previos
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // Validar ID de lección
    const leccionId = $('#edit_leccion_id').val();
    if (!leccionId) {
        errores.push('ID de lección no encontrado');
        isValid = false;
    }
    
    // 1. Validar unidad seleccionada
    const unidadId = $('#edit_unidad_id').val();
    if (!unidadId) {
        marcarError('#edit_unidad_id', 'Debe seleccionar una unidad');
        errores.push('Unidad no seleccionada');
        isValid = false;
    }
    
    // 2-6. Validar título (MISMAS VALIDACIONES QUE AGREGAR)
    const titulo = $('#edit_titulo').val().trim();
    if (!titulo) {
        marcarError('#edit_titulo', 'El título es obligatorio');
        errores.push('Título requerido');
        isValid = false;
    } else if (titulo.length < 5) {
        marcarError('#edit_titulo', 'El título debe tener al menos 5 caracteres');
        errores.push('Título muy corto (mínimo 5 caracteres)');
        isValid = false;
    } else if (titulo.length > 255) {
        marcarError('#edit_titulo', 'El título no puede superar 255 caracteres');
        errores.push('Título muy largo (máximo 255 caracteres)');
        isValid = false;
    } else if (/^\d+$/.test(titulo)) {
        marcarError('#edit_titulo', 'El título no puede ser solo números');
        errores.push('Título inválido: solo números');
        isValid = false;
    } else if ((titulo.match(/[^a-zA-Z0-9\sáéíóúñÁÉÍÓÚÑ\-\:]/g) || []).length > 5) {
        marcarError('#edit_titulo', 'El título tiene demasiados caracteres especiales');
        errores.push('Título: exceso de caracteres especiales');
        isValid = false;
    }
    
    // 7-10. Validar descripción (MISMAS VALIDACIONES)
    const descripcion = $('#edit_descripcion').val().trim();
    if (!descripcion) {
        marcarError('#edit_descripcion', 'La descripción es obligatoria');
        errores.push('Descripción requerida');
        isValid = false;
    } else if (descripcion.length < 10) {
        marcarError('#edit_descripcion', 'La descripción debe tener al menos 10 caracteres');
        errores.push('Descripción muy corta (mínimo 10 caracteres)');
        isValid = false;
    } else if (descripcion.length > 500) {
        marcarError('#edit_descripcion', 'La descripción no puede superar 500 caracteres');
        errores.push('Descripción muy larga (máximo 500 caracteres)');
        isValid = false;
    } else if (descripcion.replace(/[\s\n\r]/g, '').length < 10) {
        marcarError('#edit_descripcion', 'La descripción debe tener contenido real');
        errores.push('Descripción sin contenido válido');
        isValid = false;
    }
    
    // 11-13. Validar orden
    const orden = $('#edit_orden').val();
    if (!orden) {
        marcarError('#edit_orden', 'El orden es obligatorio');
        errores.push('Orden requerido');
        isValid = false;
    } else if (parseInt(orden) < 1 || parseInt(orden) > 100) {
        marcarError('#edit_orden', 'El orden debe estar entre 1 y 100');
        errores.push('Orden fuera de rango (1-100)');
        isValid = false;
    } else if (!Number.isInteger(parseFloat(orden))) {
        marcarError('#edit_orden', 'El orden debe ser un número entero');
        errores.push('Orden: debe ser número entero');
        isValid = false;
    }
    
    // 14. Validar tipo
    const tipo = $('#edit_tipo').val();
    if (!tipo) {
        marcarError('#edit_tipo', 'Debe seleccionar un tipo de lección');
        errores.push('Tipo de lección no seleccionado');
        isValid = false;
    }
    
    // 15-17. Validar tiempo estimado
    const tiempoEstimado = $('#edit_tiempo_estimado').val();
    if (!tiempoEstimado) {
        marcarError('#edit_tiempo_estimado', 'El tiempo estimado es obligatorio');
        errores.push('Tiempo estimado requerido');
        isValid = false;
    } else if (parseInt(tiempoEstimado) < 1 || parseInt(tiempoEstimado) > 300) {
        marcarError('#edit_tiempo_estimado', 'El tiempo debe estar entre 1 y 300 minutos');
        errores.push('Tiempo estimado fuera de rango (1-300 min)');
        isValid = false;
    } else if (!Number.isInteger(parseFloat(tiempoEstimado))) {
        marcarError('#edit_tiempo_estimado', 'El tiempo debe ser un número entero');
        errores.push('Tiempo estimado: debe ser entero');
        isValid = false;
    }
    
    // 18. Validar estado
    const estado = $('#edit_estado').val();
    if (!estado) {
        marcarError('#edit_estado', 'Debe seleccionar un estado');
        errores.push('Estado no seleccionado');
        isValid = false;
    }
    
    // 19-22. Validar contenido HTML
    const contenido = $('#edit_contenido').val();
    if (contenido) {
        if (contenido.length > 50000) {
            marcarError('#edit_contenido', 'El contenido es demasiado largo (máximo 50,000 caracteres)');
            errores.push('Contenido excede límite (50,000 caracteres)');
            isValid = false;
        }
        
        const tagsProhibidos = /<script|<iframe|<object|<embed|<link|<style/gi;
        if (tagsProhibidos.test(contenido)) {
            marcarError('#edit_contenido', 'El contenido contiene tags HTML no permitidos');
            errores.push('Contenido: tags HTML prohibidos detectados');
            isValid = false;
        }
        
        if (!validarHTMLBalanceado(contenido)) {
            marcarError('#edit_contenido', 'El HTML tiene tags desbalanceados (no cierran correctamente)');
            errores.push('HTML: tags desbalanceados');
            isValid = false;
        }
    }
    
    // 23. Validar coherencia tipo-contenido
    if (tipo === 'EVALUACION' && contenido && contenido.length > 2000) {
        marcarError('#edit_contenido', 'Las evaluaciones no deberían tener contenido tan extenso');
        errores.push('Evaluación: contenido muy extenso');
        isValid = false;
    }
    
    if (tipo === 'CONTENIDO' && (!contenido || contenido.trim().length < 50)) {
        marcarError('#edit_contenido', 'Las lecciones de contenido deben tener al menos 50 caracteres');
        errores.push('Contenido teórico insuficiente (mínimo 50 caracteres)');
        isValid = false;
    }
    
    // 24. Validar tiempo según tipo
    if (tipo === 'EVALUACION' && parseInt(tiempoEstimado) < 10) {
        marcarError('#edit_tiempo_estimado', 'Las evaluaciones deben durar al menos 10 minutos');
        errores.push('Evaluación: tiempo mínimo 10 minutos');
        isValid = false;
    }
    
    // 25. Validar publicación con contenido
    if (estado === 'PUBLICADO' && (!contenido || contenido.trim().length < 20)) {
        marcarError('#edit_contenido', 'No puede publicar una lección sin contenido suficiente');
        errores.push('Publicación: contenido insuficiente');
        isValid = false;
    }
    
    // 26-35. Validar recursos
    const recursosValidos = validarRecursosEdit();
    if (!recursosValidos.valido) {
        errores.push(...recursosValidos.errores);
        isValid = false;
    }
    
    // Mostrar errores si existen
    if (!isValid) {
        const mensaje = `❌ NO SE PUEDE ACTUALIZAR LA LECCIÓN\n\nErrores encontrados:\n\n• ${errores.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
        
        Swal.fire({
            title: 'Formulario Incompleto',
            text: mensaje,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Revisar Formulario',
            customClass: { popup: 'swal-wide' },
            footer: `Total de errores: ${errores.length}`
        });
        
        const primerError = $('.campo-error, .is-invalid').first();
        if (primerError.length) {
            primerError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => primerError.focus(), 300);
        }
    }
    
    return isValid;
}

function validarRecursosEdit() {
    const resultados = { valido: true, errores: [] };
    const recursos = [];
    $('.recurso-item-edit').each(function(index) {
        const tipo = $(this).find('.recurso-tipo-edit').val();
        const titulo = $(this).find('input[name*="[titulo]"]').val();
        const url = $(this).find('.recurso-url-edit').val();
        
        // Si hay algún campo lleno, validar todos
        if (tipo || titulo || url) {
            // Validar tipo
            if (!tipo) {
                $(this).find('.recurso-tipo-edit').addClass('campo-error');
                resultados.errores.push(`Recurso ${index + 1}: tipo requerido`);
                resultados.valido = false;
            }
            
            // Validar título
            if (!titulo || titulo.trim().length < 3) {
                $(this).find('input[name*="[titulo]"]').addClass('campo-error');
                resultados.errores.push(`Recurso ${index + 1}: título muy corto (mínimo 3 caracteres)`);
                resultados.valido = false;
            } else if (titulo.length > 100) {
                $(this).find('input[name*="[titulo]"]').addClass('campo-error');
                resultados.errores.push(`Recurso ${index + 1}: título muy largo (máximo 100)`);
                resultados.valido = false;
            }
            
            // Validar URL
            if (!url) {
                $(this).find('.recurso-url-edit').addClass('campo-error');
                resultados.errores.push(`Recurso ${index + 1}: URL requerida`);
                resultados.valido = false;
            } else if (!isValidURL(url)) {
                $(this).find('.recurso-url-edit').addClass('campo-error');
                resultados.errores.push(`Recurso ${index + 1}: URL inválida`);
                resultados.valido = false;
            } else if (url.length > 500) {
                $(this).find('.recurso-url-edit').addClass('campo-error');
                resultados.errores.push(`Recurso ${index + 1}: URL muy larga (máximo 500)`);
                resultados.valido = false;
            }
            
            // Validar coherencia tipo-URL
            if (tipo === 'VIDEO' && url && !url.includes('youtube') && !url.includes('vimeo')
                && !url.includes('youtu.be') && !url.includes('.mp4')) {
                $(this).find('.recurso-url-edit').addClass('campo-error');
                resultados.errores.push(`Recurso ${index + 1}: URL no parece ser de video`);
                resultados.valido = false;
            }
            
            if (tipo === 'PDF' && url && !url.toLowerCase().includes('.pdf')) {
                $(this).find('.recurso-url-edit').addClass('campo-error');
                resultados.errores.push(`Recurso ${index + 1}: URL no parece ser PDF`);
                resultados.valido = false;
            }
            
            recursos.push({ tipo, titulo, url });
        }
    });
    
    // Validar límite de recursos
    if (recursos.length > 10) {
        resultados.errores.push('Máximo 10 recursos permitidos');
        resultados.valido = false;
    }
    
    // Validar URLs duplicadas
    const urlsSet = new Set();
    recursos.forEach((recurso, index) => {
        if (urlsSet.has(recurso.url)) {
            resultados.errores.push(`Recurso ${index + 1}: URL duplicada`);
            resultados.valido = false;
        }
        urlsSet.add(recurso.url);
    });
    
    return resultados;
}

function cargarDatosEdicionLeccion(leccion) {
    // Cargar datos básicos
    $('#edit_leccion_id').val(leccion.id);
    $('#edit_unidad_id').val(leccion.unidad_id);
    $('#edit_titulo').val(leccion.titulo);
    $('#edit_descripcion').val(leccion.descripcion);
    $('#edit_orden').val(leccion.orden);
    $('#edit_tipo').val(leccion.tipo);
    $('#edit_contenido').val(leccion.contenido || '');
    
    // Cargar configuraciones
    const config = leccion.configuraciones || {};
    $('#edit_tiempo_estimado').val(config.tiempo_estimado || 45);
    $('#edit_estado').val(config.estado || 'BORRADOR');
    $('#edit_obligatorio').prop('checked', config.obligatorio || false);
    
    // Actualizar contadores
    $('#edit_desc_count').text(`${leccion.descripcion.length}/500`);
    $('#edit_contenido_count').text(`${(leccion.contenido || '').length} caracteres`);
    
    // Cargar recursos
    const recursos = leccion.recursos || [];
    $('#editRecursosContainer').html('');
    editRecursoIndex = 0;
    
    if (recursos.length === 0) {
        agregarRecursoEdit();
    } else {
        recursos.forEach((recurso, index) => {
            agregarRecursoEdit(recurso);
        });
    }
}

function agregarRecursoEdit(datos = null) {
    const recursosActuales = $('.recurso-item-edit').length;
    if (recursosActuales >= 10) {
        Swal.fire({
            title: 'Límite Alcanzado',
            text: 'Solo puede agregar hasta 10 recursos por lección',
            icon: 'warning',
            confirmButtonColor: '#fd7e14'
        });
        return;
    }
    
    const tipo = datos ? datos.tipo : '';
    const titulo = datos ? datos.titulo : '';
    const url = datos ? datos.url : '';
    
    const nuevoRecurso = `
        <div class="recurso-item-edit border rounded p-3 mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Tipo de Recurso</label>
                    <select class="form-select recurso-tipo-edit" name="recursos_edit[${editRecursoIndex}][tipo]">
                        <option value="">Seleccionar</option>
                        <option value="PDF" ${tipo === 'PDF' ? 'selected' : ''}>Documento PDF</option>
                        <option value="VIDEO" ${tipo === 'VIDEO' ? 'selected' : ''}>Video</option>
                        <option value="ENLACE" ${tipo === 'ENLACE' ? 'selected' : ''}>Enlace Web</option>
                        <option value="IMAGEN" ${tipo === 'IMAGEN' ? 'selected' : ''}>Imagen</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Título del Recurso</label>
                    <input type="text" class="form-control" name="recursos_edit[${editRecursoIndex}][titulo]" 
                           placeholder="Ej: Material de apoyo" maxlength="100" value="${titulo}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">URL del Recurso</label>
                    <input type="url" class="form-control recurso-url-edit" name="recursos_edit[${editRecursoIndex}][url]" 
                           placeholder="https://..." maxlength="500" value="${url}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm" 
                            onclick="eliminarRecursoEdit(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#editRecursosContainer').append(nuevoRecurso);
    editRecursoIndex++;
}

function eliminarRecursoEdit(btn) {
    const recursosCount = $('.recurso-item-edit').length;
    if (recursosCount <= 1) {
        Swal.fire({
            title: 'No se puede eliminar',
            text: 'Debe mantener al menos una sección de recursos. Puede dejarla vacía si no necesita recursos.',
            icon: 'info',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }
    
    $(btn).closest('.recurso-item-edit').remove();
}

function procesarRecursosEdit() {
    const recursos = [];
    let orden = 1;
    
    $('.recurso-item-edit').each(function() {
        const tipo = $(this).find('.recurso-tipo-edit').val();
        const titulo = $(this).find('input[name*="[titulo]"]').val();
        const url = $(this).find('.recurso-url-edit').val();
        
        if (tipo && titulo && url) {
            recursos.push({
                tipo: tipo,
                titulo: titulo.trim(),
                url: url.trim(),
                orden: orden,
                descargable: tipo === 'PDF'
            });
            orden++;
        }
    });
    
    return recursos;
}
</script>