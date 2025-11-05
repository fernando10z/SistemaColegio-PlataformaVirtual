<!-- Modal Agregar Lección -->
<div class="modal fade" id="modalAgregarLeccion" tabindex="-1" aria-labelledby="modalAgregarLeccionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalAgregarLeccionLabel">
                    <i class="ti ti-book-2 me-2"></i>
                    Nueva Lección
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAgregarLeccion" method="POST">
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
                                            <label for="add_unidad_id" class="form-label">
                                                Unidad <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_unidad_id" name="unidad_id" required>
                                                <option value="">Seleccionar unidad</option>
                                                <?php foreach ($unidades as $unidad): ?>
                                                    <option value="<?= $unidad['id'] ?>">
                                                        <?= htmlspecialchars($unidad['curso_nombre']) ?> › <?= htmlspecialchars($unidad['titulo']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="add_titulo" class="form-label">
                                                Título de la Lección <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_titulo" name="titulo" 
                                                   placeholder="Ej: Introducción a los Números Enteros" required 
                                                   maxlength="255" minlength="5">
                                            <div class="form-text">Mínimo 5 caracteres, máximo 255</div>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="add_orden" class="form-label">
                                                Orden <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="add_orden" name="orden" 
                                                   required min="1" max="100" value="1">
                                            <div class="form-text">1-100</div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="add_descripcion" class="form-label">
                                                Descripción <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control" id="add_descripcion" name="descripcion" 
                                                      rows="3" placeholder="Descripción breve de la lección" 
                                                      required minlength="10" maxlength="500"></textarea>
                                            <div class="form-text">Entre 10 y 500 caracteres. <span id="add_desc_count">0/500</span></div>
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
                                            <label for="add_tipo" class="form-label">
                                                Tipo de Lección <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_tipo" name="tipo" required>
                                                <option value="">Seleccionar tipo</option>
                                                <option value="CONTENIDO">Contenido Teórico</option>
                                                <option value="ACTIVIDAD">Actividad Práctica</option>
                                                <option value="EVALUACION">Evaluación</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_tiempo_estimado" class="form-label">
                                                Tiempo Estimado (min) <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="add_tiempo_estimado" 
                                                   name="tiempo_estimado" required min="1" max="300" value="45">
                                            <div class="form-text">1-300 minutos</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="add_estado" class="form-label">
                                                Estado <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_estado" name="estado" required>
                                                <option value="BORRADOR">Borrador</option>
                                                <option value="PUBLICADO">Publicado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label d-block">Opciones</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="add_obligatorio" 
                                                       name="obligatorio" value="1" checked>
                                                <label class="form-check-label" for="add_obligatorio">
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
                                        <label for="add_contenido" class="form-label">
                                            Contenido HTML
                                        </label>
                                        <textarea class="form-control" id="add_contenido" name="contenido" 
                                                  rows="12" placeholder="<h2>Título del contenido</h2>
<p>Texto del contenido...</p>

<ul>
  <li>Punto 1</li>
  <li>Punto 2</li>
</ul>"></textarea>
                                        <div class="form-text">
                                            Puede usar HTML básico. Tags permitidos: h2, h3, p, strong, em, ul, ol, li, br
                                            <br><span id="add_contenido_count">0 caracteres</span>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>
                                        <strong>Recomendaciones:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Use títulos (h2, h3) para organizar el contenido</li>
                                            <li>Mantenga párrafos cortos y claros</li>
                                            <li>Use listas para puntos clave</li>
                                            <li>El contenido puede dejarse vacío si es una evaluación externa</li>
                                        </ul>
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
                                    <div id="recursosContainer">
                                        <div class="recurso-item border rounded p-3 mb-3">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="form-label">Tipo de Recurso</label>
                                                    <select class="form-select recurso-tipo" name="recursos[0][tipo]">
                                                        <option value="">Seleccionar</option>
                                                        <option value="PDF">Documento PDF</option>
                                                        <option value="VIDEO">Video</option>
                                                        <option value="ENLACE">Enlace Web</option>
                                                        <option value="IMAGEN">Imagen</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Título del Recurso</label>
                                                    <input type="text" class="form-control" name="recursos[0][titulo]" 
                                                           placeholder="Ej: Material de apoyo" maxlength="100">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">URL del Recurso</label>
                                                    <input type="url" class="form-control recurso-url" name="recursos[0][url]" 
                                                           placeholder="https://..." maxlength="500">
                                                </div>
                                                <div class="col-md-1 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                                            onclick="eliminarRecurso(this)">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="agregarRecurso()">
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarLeccion">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Lección
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

.recurso-item {
    background-color: #f8f9fa;
}

.swal-wide {
    width: 650px !important;
}
</style>

<Script src="https://code.jquery.com/jquery-3.6.0.min.js"></Script>

<script>
let recursoIndex = 1;

$(document).ready(function() {
    // Contador de caracteres para descripción
    $('#add_descripcion').on('input', function() {
        const count = $(this).val().length;
        $('#add_desc_count').text(`${count}/500`);
        if (count > 500) {
            $(this).addClass('campo-error');
        } else {
            $(this).removeClass('campo-error');
        }
    });

    // Contador de caracteres para contenido
    $('#add_contenido').on('input', function() {
        const count = $(this).val().length;
        $('#add_contenido_count').text(`${count} caracteres`);
    });

    // Validación de URLs en tiempo real
    $(document).on('input', '.recurso-url', function() {
        const url = $(this).val();
        if (url && !isValidURL(url)) {
            $(this).addClass('campo-error');
        } else {
            $(this).removeClass('campo-error');
        }
    });

    // Envío del formulario con validaciones
    $('#formAgregarLeccion').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioAgregarLeccion()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        // Procesar recursos
        const recursos = procesarRecursos();
        formData.set('recursos', JSON.stringify(recursos));
        
        mostrarCarga();
        $('#btnGuardarLeccion').prop('disabled', true);

        $.ajax({
            url: 'modales/lecciones/procesar_lecciones.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarLeccion').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Lección Creada!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAgregarLeccion').modal('hide');
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
                $('#btnGuardarLeccion').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalAgregarLeccion').on('hidden.bs.modal', function() {
        limpiarFormularioAgregar();
    });
});

// FUNCIÓN DE VALIDACIÓN COMPLETA CON 35 VALIDACIONES
function validarFormularioAgregarLeccion() {
    let isValid = true;
    let errores = [];
    
    // Limpiar errores previos
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // 1. Validar unidad seleccionada
    const unidadId = $('#add_unidad_id').val();
    if (!unidadId) {
        marcarError('#add_unidad_id', 'Debe seleccionar una unidad');
        errores.push('Unidad no seleccionada');
        isValid = false;
    }
    
    // 2-4. Validar título (obligatorio, longitud 5-255, no solo espacios)
    const titulo = $('#add_titulo').val().trim();
    if (!titulo) {
        marcarError('#add_titulo', 'El título es obligatorio');
        errores.push('Título requerido');
        isValid = false;
    } else if (titulo.length < 5) {
        marcarError('#add_titulo', 'El título debe tener al menos 5 caracteres');
        errores.push('Título muy corto (mínimo 5 caracteres)');
        isValid = false;
    } else if (titulo.length > 255) {
        marcarError('#add_titulo', 'El título no puede superar 255 caracteres');
        errores.push('Título muy largo (máximo 255 caracteres)');
        isValid = false;
    }
    
    // 5. Validar que el título no sea solo números
    if (titulo && /^\d+$/.test(titulo)) {
        marcarError('#add_titulo', 'El título no puede ser solo números');
        errores.push('Título inválido: solo números');
        isValid = false;
    }
    
    // 6. Validar que el título no tenga caracteres especiales excesivos
    if (titulo && (titulo.match(/[^a-zA-Z0-9\sáéíóúñÁÉÍÓÚÑ\-\:]/g) || []).length > 5) {
        marcarError('#add_titulo', 'El título tiene demasiados caracteres especiales');
        errores.push('Título: exceso de caracteres especiales');
        isValid = false;
    }
    
    // 7-9. Validar descripción (obligatoria, longitud 10-500)
    const descripcion = $('#add_descripcion').val().trim();
    if (!descripcion) {
        marcarError('#add_descripcion', 'La descripción es obligatoria');
        errores.push('Descripción requerida');
        isValid = false;
    } else if (descripcion.length < 10) {
        marcarError('#add_descripcion', 'La descripción debe tener al menos 10 caracteres');
        errores.push('Descripción muy corta (mínimo 10 caracteres)');
        isValid = false;
    } else if (descripcion.length > 500) {
        marcarError('#add_descripcion', 'La descripción no puede superar 500 caracteres');
        errores.push('Descripción muy larga (máximo 500 caracteres)');
        isValid = false;
    }
    
    // 10. Validar que la descripción no sea solo espacios o saltos de línea
    if (descripcion && descripcion.replace(/[\s\n\r]/g, '').length < 10) {
        marcarError('#add_descripcion', 'La descripción debe tener contenido real');
        errores.push('Descripción sin contenido válido');
        isValid = false;
    }
    
    // 11-13. Validar orden (obligatorio, rango 1-100, número entero)
    const orden = $('#add_orden').val();
    if (!orden) {
        marcarError('#add_orden', 'El orden es obligatorio');
        errores.push('Orden requerido');
        isValid = false;
    } else if (parseInt(orden) < 1 || parseInt(orden) > 100) {
        marcarError('#add_orden', 'El orden debe estar entre 1 y 100');
        errores.push('Orden fuera de rango (1-100)');
        isValid = false;
    } else if (!Number.isInteger(parseFloat(orden))) {
        marcarError('#add_orden', 'El orden debe ser un número entero');
        errores.push('Orden: debe ser número entero');
        isValid = false;
    }
    
    // 14. Validar tipo de lección
    const tipo = $('#add_tipo').val();
    if (!tipo) {
        marcarError('#add_tipo', 'Debe seleccionar un tipo de lección');
        errores.push('Tipo de lección no seleccionado');
        isValid = false;
    }
    
    // 15-17. Validar tiempo estimado (obligatorio, rango 1-300, entero)
    const tiempoEstimado = $('#add_tiempo_estimado').val();
    if (!tiempoEstimado) {
        marcarError('#add_tiempo_estimado', 'El tiempo estimado es obligatorio');
        errores.push('Tiempo estimado requerido');
        isValid = false;
    } else if (parseInt(tiempoEstimado) < 1 || parseInt(tiempoEstimado) > 300) {
        marcarError('#add_tiempo_estimado', 'El tiempo debe estar entre 1 y 300 minutos');
        errores.push('Tiempo estimado fuera de rango (1-300 min)');
        isValid = false;
    } else if (!Number.isInteger(parseFloat(tiempoEstimado))) {
        marcarError('#add_tiempo_estimado', 'El tiempo debe ser un número entero');
        errores.push('Tiempo estimado: debe ser entero');
        isValid = false;
    }
    
    // 18. Validar estado
    const estado = $('#add_estado').val();
    if (!estado) {
        marcarError('#add_estado', 'Debe seleccionar un estado');
        errores.push('Estado no seleccionado');
        isValid = false;
    }
    
    // 19-20. Validar contenido HTML (si existe, verificar tags permitidos)
    const contenido = $('#add_contenido').val();
    if (contenido) {
        // Validar longitud máxima del contenido
        if (contenido.length > 50000) {
            marcarError('#add_contenido', 'El contenido es demasiado largo (máximo 50,000 caracteres)');
            errores.push('Contenido excede límite (50,000 caracteres)');
            isValid = false;
        }
        
        // Validar tags HTML permitidos
        const tagsProhibidos = /<script|<iframe|<object|<embed|<link|<style/gi;
        if (tagsProhibidos.test(contenido)) {
            marcarError('#add_contenido', 'El contenido contiene tags HTML no permitidos');
            errores.push('Contenido: tags HTML prohibidos detectados');
            isValid = false;
        }
    }
    
    // 21. Validar coherencia: si es EVALUACION, el contenido no debería ser muy extenso
    if (tipo === 'EVALUACION' && contenido && contenido.length > 2000) {
        marcarError('#add_contenido', 'Las evaluaciones no deberían tener contenido tan extenso');
        errores.push('Evaluación: contenido muy extenso');
        isValid = false;
    }
    
    // 22. Validar coherencia: si es CONTENIDO, debería tener contenido
    if (tipo === 'CONTENIDO' && (!contenido || contenido.trim().length < 50)) {
        marcarError('#add_contenido', 'Las lecciones de contenido deben tener al menos 50 caracteres');
        errores.push('Contenido teórico insuficiente (mínimo 50 caracteres)');
        isValid = false;
    }
    
    // 23-30. Validar recursos
    const recursosValidos = validarRecursos();
    if (!recursosValidos.valido) {
        errores.push(...recursosValidos.errores);
        isValid = false;
    }
    
    // 31. Validar que el título no sea duplicado (similar a otro)
    if (titulo) {
        const tituloNormalizado = titulo.toLowerCase().replace(/\s+/g, '');
        $('.leccion-item').each(function() {
            const tituloExistente = $(this).find('.leccion-titulo').text().toLowerCase().replace(/\s+/g, '');
            if (tituloNormalizado === tituloExistente) {
                marcarError('#add_titulo', 'Ya existe una lección con un título similar');
                errores.push('Título duplicado o muy similar');
                isValid = false;
                return false; // Break
            }
        });
    }
    
    // 32. Validar tiempo estimado según tipo
    if (tipo === 'EVALUACION' && parseInt(tiempoEstimado) < 10) {
        marcarError('#add_tiempo_estimado', 'Las evaluaciones deben durar al menos 10 minutos');
        errores.push('Evaluación: tiempo mínimo 10 minutos');
        isValid = false;
    }
    
    // 33. Validar que si está publicado, tenga contenido suficiente
    if (estado === 'PUBLICADO') {
        if (!contenido || contenido.trim().length < 20) {
            marcarError('#add_contenido', 'No puede publicar una lección sin contenido suficiente');
            errores.push('Publicación: contenido insuficiente');
            isValid = false;
        }
    }
    
    // 34. Validar coherencia entre descripción y título
    if (titulo && descripcion) {
        const palabrasTitulo = titulo.toLowerCase().split(/\s+/).filter(p => p.length > 3);
        const palabrasDesc = descripcion.toLowerCase();
        const coincidencias = palabrasTitulo.filter(p => palabrasDesc.includes(p));
        
        if (coincidencias.length === 0 && palabrasTitulo.length > 2) {
            // Warning, no error crítico
            console.warn('La descripción no parece relacionada con el título');
        }
    }
    
    // 35. Validar que el contenido HTML esté balanceado (tags abren y cierran)
    if (contenido && contenido.includes('<')) {
        if (!validarHTMLBalanceado(contenido)) {
            marcarError('#add_contenido', 'El HTML tiene tags desbalanceados (no cierran correctamente)');
            errores.push('HTML: tags desbalanceados');
            isValid = false;
        }
    }
    
    // Mostrar resumen de errores
    if (!isValid) {
        const mensaje = `❌ NO SE PUEDE CREAR LA LECCIÓN\n\nErrores encontrados:\n\n• ${errores.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
        
        Swal.fire({
            title: 'Formulario Incompleto',
            text: mensaje,
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Revisar Formulario',
            customClass: { popup: 'swal-wide' },
            footer: `Total de errores: ${errores.length}`
        });
        
        // Scroll al primer error
        const primerError = $('.campo-error, .is-invalid').first();
        if (primerError.length) {
            primerError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => primerError.focus(), 300);
        }
    }
    
    return isValid;
}

function validarRecursos() {
    const resultados = { valido: true, errores: [] };
    const recursos = [];
    
    $('.recurso-item').each(function(index) {
        const tipo = $(this).find('.recurso-tipo').val();
        const titulo = $(this).find('input[name*="[titulo]"]').val();
        const url = $(this).find('.recurso-url').val();
        
        // Si hay algún campo lleno, validar todos
        if (tipo || titulo || url) {
            // Validar tipo
            if (!tipo) {
                $(this).find('.recurso-tipo').addClass('campo-error');
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
                $(this).find('.recurso-url').addClass('campo-error');
                resultados.errores.push(`Recurso ${index + 1}: URL requerida`);
                resultados.valido = false;
            } else if (!isValidURL(url)) {
                $(this).find('.recurso-url').addClass('campo-error');
                resultados.errores.push(`Recurso ${index + 1}: URL inválida`);
                resultados.valido = false;
            } else if (url.length > 500) {
                $(this).find('.recurso-url').addClass('campo-error');
                resultados.errores.push(`Recurso ${index + 1}: URL muy larga (máximo 500)`);
                resultados.valido = false;
            }
            
            // Validar coherencia tipo-URL
            if (tipo === 'VIDEO' && url && !url.includes('youtube') && !url.includes('vimeo')
                && !url.includes('youtu.be') && !url.includes('.mp4')) {
                $(this).find('.recurso-url').addClass('campo-error');
                resultados.errores.push(`Recurso ${index + 1}: URL no parece ser de video`);
                resultados.valido = false;
            }
            
            if (tipo === 'PDF' && url && !url.toLowerCase().includes('.pdf')) {
                $(this).find('.recurso-url').addClass('campo-error');
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

function validarHTMLBalanceado(html) {
    const tagPattern = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi;
    const stack = [];
    let match;
    
    while ((match = tagPattern.exec(html)) !== null) {
        const tag = match[1].toLowerCase();
        const isClosing = match[0].startsWith('</');
        
        // Tags auto-cerrados
        if (['br', 'hr', 'img'].includes(tag)) continue;
        
        if (isClosing) {
            if (stack.length === 0 || stack[stack.length - 1] !== tag) {
                return false;
            }
            stack.pop();
        } else {
            stack.push(tag);
        }
    }
    
    return stack.length === 0;
}

function isValidURL(string) {
    try {
        const url = new URL(string);
        return url.protocol === "http:" || url.protocol === "https:";
    } catch (_) {
        return false;
    }
}

function marcarError(selector, mensaje) {
    const campo = $(selector);
    campo.addClass('is-invalid campo-error');
    campo.after(`<div class="invalid-feedback">${mensaje}</div>`);
}

function agregarRecurso() {
    const recursosActuales = $('.recurso-item').length;
    if (recursosActuales >= 10) {
        Swal.fire({
            title: 'Límite Alcanzado',
            text: 'Solo puede agregar hasta 10 recursos por lección',
            icon: 'warning',
            confirmButtonColor: '#fd7e14'
        });
        return;
    }
    
    const nuevoRecurso = `
        <div class="recurso-item border rounded p-3 mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Tipo de Recurso</label>
                    <select class="form-select recurso-tipo" name="recursos[${recursoIndex}][tipo]">
                        <option value="">Seleccionar</option>
                        <option value="PDF">Documento PDF</option>
                        <option value="VIDEO">Video</option>
                        <option value="ENLACE">Enlace Web</option>
                        <option value="IMAGEN">Imagen</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Título del Recurso</label>
                    <input type="text" class="form-control" name="recursos[${recursoIndex}][titulo]" 
                           placeholder="Ej: Material de apoyo" maxlength="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label">URL del Recurso</label>
                    <input type="url" class="form-control recurso-url" name="recursos[${recursoIndex}][url]" 
                           placeholder="https://..." maxlength="500">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm" 
                            onclick="eliminarRecurso(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#recursosContainer').append(nuevoRecurso);
    recursoIndex++;
}

function eliminarRecurso(btn) {
    const recursosCount = $('.recurso-item').length;
    if (recursosCount <= 1) {
        Swal.fire({
            title: 'No se puede eliminar',
            text: 'Debe mantener al menos una sección de recursos. Puede dejarla vacía si no necesita recursos.',
            icon: 'info',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }
    
    $(btn).closest('.recurso-item').remove();
}

function procesarRecursos() {
    const recursos = [];
    let orden = 1;
    
    $('.recurso-item').each(function() {
        const tipo = $(this).find('.recurso-tipo').val();
        const titulo = $(this).find('input[name*="[titulo]"]').val();
        const url = $(this).find('.recurso-url').val();
        
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

function limpiarFormularioAgregar() {
    $('#formAgregarLeccion')[0].reset();
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    $('#add_desc_count').text('0/500');
    $('#add_contenido_count').text('0 caracteres');
    
    // Resetear recursos a uno solo
    $('#recursosContainer').html(`
        <div class="recurso-item border rounded p-3 mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Tipo de Recurso</label>
                    <select class="form-select recurso-tipo" name="recursos[0][tipo]">
                        <option value="">Seleccionar</option>
                        <option value="PDF">Documento PDF</option>
                        <option value="VIDEO">Video</option>
                        <option value="ENLACE">Enlace Web</option>
                        <option value="IMAGEN">Imagen</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Título del Recurso</label>
                    <input type="text" class="form-control" name="recursos[0][titulo]" 
                           placeholder="Ej: Material de apoyo" maxlength="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label">URL del Recurso</label>
                    <input type="url" class="form-control recurso-url" name="recursos[0][url]" 
                           placeholder="https://..." maxlength="500">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm" 
                            onclick="eliminarRecurso(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `);
    recursoIndex = 1;
}
</script>