<!-- Modal Vincular Recurso -->
<div class="modal fade" id="modalVincularRecurso" tabindex="-1" aria-labelledby="modalVincularRecursoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="modalVincularRecursoLabel">
                    <i class="ti ti-link me-2"></i>
                    Vincular Recurso a Cursos y Lecciones
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formVincularRecurso" method="POST">
                <input type="hidden" id="vincular_recurso_id" name="recurso_id">
                
                <div class="modal-body">
                    <!-- Información del Recurso -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-dark">
                                <i class="ti ti-info-circle me-2"></i>
                                Recurso Seleccionado
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="vincular_recurso_info" class="alert alert-info mb-0">
                                <!-- Se llenará dinámicamente -->
                            </div>
                        </div>
                    </div>

                    <!-- Tipo de Vinculación -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-dark">
                                <i class="ti ti-settings me-2"></i>
                                Tipo de Vinculación
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">
                                        ¿Dónde deseas vincular este recurso? <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="vincular_tipo" name="tipo_vinculacion" required>
                                        <option value="">Seleccionar tipo</option>
                                        <option value="CURSO">A un Curso completo</option>
                                        <option value="LECCION">A una Lección específica</option>
                                        <option value="AMBOS">A Curso y Lección</option>
                                    </select>
                                    <div class="form-text">
                                        <strong>Curso:</strong> Disponible en todo el curso.
                                        <strong>Lección:</strong> Solo en esa lección específica.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selección de Curso -->
                    <div class="card border-0 shadow-sm mb-3" id="seccion_vincular_curso" style="display: none;">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-dark">
                                <i class="ti ti-book me-2"></i>
                                Seleccionar Curso
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="vincular_curso" class="form-label">
                                        Curso <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="vincular_curso" name="curso_id">
                                        <option value="">Seleccionar curso</option>
                                        <?php foreach ($cursos_disponibles as $curso): ?>
                                            <option value="<?= $curso['id'] ?>">
                                                <?= htmlspecialchars($curso['area_nombre']) ?> - 
                                                <?= htmlspecialchars($curso['grado']) ?><?= htmlspecialchars($curso['seccion']) ?> - 
                                                <?= htmlspecialchars($curso['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="vincular_orden_curso" class="form-label">
                                        Orden de Aparición
                                    </label>
                                    <input type="number" class="form-control" id="vincular_orden_curso" 
                                           name="orden_curso" min="1" max="100" value="1">
                                    <div class="form-text">Posición en la lista de recursos del curso</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Disponibilidad en Curso</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="vincular_destacado_curso" name="destacado_curso" value="1">
                                        <label class="form-check-label" for="vincular_destacado_curso">
                                            Recurso Destacado
                                        </label>
                                    </div>
                                    <div class="form-text">Aparecerá en la sección principal</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selección de Lección -->
                    <div class="card border-0 shadow-sm mb-3" id="seccion_vincular_leccion" style="display: none;">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-dark">
                                <i class="ti ti-file-text me-2"></i>
                                Seleccionar Lección
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="vincular_curso_para_leccion" class="form-label">
                                        Primero selecciona el curso <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="vincular_curso_para_leccion">
                                        <option value="">Seleccionar curso</option>
                                        <?php foreach ($cursos_disponibles as $curso): ?>
                                            <option value="<?= $curso['id'] ?>">
                                                <?= htmlspecialchars($curso['area_nombre']) ?> - 
                                                <?= htmlspecialchars($curso['grado']) ?><?= htmlspecialchars($curso['seccion']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="vincular_unidad" class="form-label">
                                        Luego selecciona la unidad <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="vincular_unidad">
                                        <option value="">Primero selecciona un curso</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="vincular_leccion" class="form-label">
                                        Finalmente la lección <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="vincular_leccion" name="leccion_id">
                                        <option value="">Primero selecciona una unidad</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="vincular_orden_leccion" class="form-label">
                                        Orden de Aparición
                                    </label>
                                    <input type="number" class="form-control" id="vincular_orden_leccion" 
                                           name="orden_leccion" min="1" max="100" value="1">
                                    <div class="form-text">Posición en la lista de recursos de la lección</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Configuración</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="vincular_obligatorio" name="obligatorio" value="1">
                                        <label class="form-check-label" for="vincular_obligatorio">
                                            Recurso Obligatorio
                                        </label>
                                    </div>
                                    <div class="form-text">Estudiante debe revisarlo para avanzar</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vinculaciones Actuales -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-dark">
                                <i class="ti ti-list me-2"></i>
                                Vinculaciones Actuales
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="vinculaciones_actuales_container">
                                <div class="text-center text-muted py-3">
                                    <i class="ti ti-unlink" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2">Sin vinculaciones actuales</p>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarVinculacion">
                        <i class="ti ti-link me-2"></i>
                        Guardar Vinculación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.vinculacion-item {
    padding: 10px;
    margin-bottom: 10px;
    background-color: #f8f9fa;
    border-left: 3px solid #667eea;
    border-radius: 4px;
}

.vinculacion-item:hover {
    background-color: #e9ecef;
}

.btn-desvincular {
    padding: 2px 8px;
    font-size: 0.75rem;
}
</style>

<script>
$(document).ready(function() {
    // Mostrar/ocultar secciones según tipo de vinculación
    $('#vincular_tipo').on('change', function() {
        const tipo = $(this).val();
        
        $('#seccion_vincular_curso').hide();
        $('#seccion_vincular_leccion').hide();
        $('#vincular_curso').prop('required', false);
        $('#vincular_leccion').prop('required', false);
        
        if (tipo === 'CURSO' || tipo === 'AMBOS') {
            $('#seccion_vincular_curso').slideDown();
            $('#vincular_curso').prop('required', true);
        }
        
        if (tipo === 'LECCION' || tipo === 'AMBOS') {
            $('#seccion_vincular_leccion').slideDown();
            $('#vincular_leccion').prop('required', true);
        }
    });

    // Cargar unidades al seleccionar curso
    $('#vincular_curso_para_leccion').on('change', function() {
        const cursoId = $(this).val();
        
        $('#vincular_unidad').html('<option value="">Cargando unidades...</option>');
        $('#vincular_leccion').html('<option value="">Primero selecciona una unidad</option>');
        
        if (!cursoId) {
            $('#vincular_unidad').html('<option value="">Primero selecciona un curso</option>');
            return;
        }
        
        cargarUnidadesPorCurso(cursoId);
    });

    // Cargar lecciones al seleccionar unidad
    $('#vincular_unidad').on('change', function() {
        const unidadId = $(this).val();
        
        $('#vincular_leccion').html('<option value="">Cargando lecciones...</option>');
        
        if (!unidadId) {
            $('#vincular_leccion').html('<option value="">Primero selecciona una unidad</option>');
            return;
        }
        
        cargarLeccionesPorUnidad(unidadId);
    });

    // Envío del formulario
    $('#formVincularRecurso').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioVinculacion()) {
            return false;
        }

        const formData = $(this).serialize() + '&accion=vincular';
        
        mostrarCarga();
        $('#btnGuardarVinculacion').prop('disabled', true);

        $.ajax({
            url: 'modales/recursos/procesar_recursos.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarVinculacion').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Vinculación Exitosa!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        // Recargar vinculaciones actuales
                        const recursoId = $('#vincular_recurso_id').val();
                        cargarVinculacionesActuales(recursoId);
                        
                        // Limpiar formulario parcialmente
                        $('#vincular_tipo').val('');
                        $('#vincular_curso').val('');
                        $('#vincular_leccion').val('');
                        $('#seccion_vincular_curso').hide();
                        $('#seccion_vincular_leccion').hide();
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
                $('#btnGuardarVinculacion').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar al cerrar
    $('#modalVincularRecurso').on('hidden.bs.modal', function() {
        limpiarFormularioVinculacion();
    });
});

// VALIDACIÓN COMPLETA DEL FORMULARIO DE VINCULACIÓN (15 VALIDACIONES)
function validarFormularioVinculacion() {
    let isValid = true;
    let erroresEncontrados = [];
    
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    
    // 1. Validar que se haya seleccionado un recurso
    const recursoId = $('#vincular_recurso_id').val();
    if (!recursoId) {
        erroresEncontrados.push('No se ha seleccionado ningún recurso');
        isValid = false;
    }
    
    // 2. Validar tipo de vinculación
    const tipo = $('#vincular_tipo').val();
    if (!tipo) {
        marcarCampoError('#vincular_tipo', 'Debe seleccionar un tipo de vinculación');
        erroresEncontrados.push('Tipo de vinculación requerido');
        isValid = false;
    }
    
    // 3-7. Validaciones para vinculación a CURSO
    if (tipo === 'CURSO' || tipo === 'AMBOS') {
        const cursoId = $('#vincular_curso').val();
        if (!cursoId) {
            marcarCampoError('#vincular_curso', 'Debe seleccionar un curso');
            erroresEncontrados.push('Curso requerido');
            isValid = false;
        }
        
        const ordenCurso = $('#vincular_orden_curso').val();
        if (ordenCurso && (parseInt(ordenCurso) < 1 || parseInt(ordenCurso) > 100)) {
            marcarCampoError('#vincular_orden_curso', 'El orden debe estar entre 1 y 100');
            erroresEncontrados.push('Orden de curso: valor fuera de rango');
            isValid = false;
        }
    }
    
    // 8-15. Validaciones para vinculación a LECCIÓN
    if (tipo === 'LECCION' || tipo === 'AMBOS') {
        const cursoParaLeccion = $('#vincular_curso_para_leccion').val();
        if (!cursoParaLeccion) {
            marcarCampoError('#vincular_curso_para_leccion', 'Debe seleccionar un curso');
            erroresEncontrados.push('Curso para lección requerido');
            isValid = false;
        }
        
        const unidadId = $('#vincular_unidad').val();
        if (!unidadId) {
            marcarCampoError('#vincular_unidad', 'Debe seleccionar una unidad');
            erroresEncontrados.push('Unidad requerida');
            isValid = false;
        }
        
        const leccionId = $('#vincular_leccion').val();
        if (!leccionId) {
            marcarCampoError('#vincular_leccion', 'Debe seleccionar una lección');
            erroresEncontrados.push('Lección requerida');
            isValid = false;
        }
        
        const ordenLeccion = $('#vincular_orden_leccion').val();
        if (ordenLeccion && (parseInt(ordenLeccion) < 1 || parseInt(ordenLeccion) > 100)) {
            marcarCampoError('#vincular_orden_leccion', 'El orden debe estar entre 1 y 100');
            erroresEncontrados.push('Orden de lección: valor fuera de rango');
            isValid = false;
        }
    }
    
    if (!isValid) {
        const mensajeError = `❌ NO SE PUEDE CREAR LA VINCULACIÓN\n\nErrores encontrados:\n\n• ${erroresEncontrados.join('\n• ')}\n\n⚠️ Corrija todos los errores para continuar.`;
        
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

// FUNCIÓN PARA CARGAR UNIDADES
function cargarUnidadesPorCurso(cursoId) {
    $.ajax({
        url: 'modales/recursos/procesar_recursos.php',
        type: 'POST',
        data: { accion: 'obtener_unidades', curso_id: cursoId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let opciones = '<option value="">Seleccionar unidad</option>';
                response.unidades.forEach(unidad => {
                    opciones += `<option value="${unidad.id}">${unidad.titulo}</option>`;
                });
                $('#vincular_unidad').html(opciones);
            } else {
                $('#vincular_unidad').html('<option value="">No hay unidades disponibles</option>');
            }
        },
        error: function() {
            $('#vincular_unidad').html('<option value="">Error al cargar unidades</option>');
        }
    });
}

// FUNCIÓN PARA CARGAR LECCIONES
function cargarLeccionesPorUnidad(unidadId) {
    $.ajax({
        url: 'modales/recursos/procesar_recursos.php',
        type: 'POST',
        data: { accion: 'obtener_lecciones', unidad_id: unidadId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let opciones = '<option value="">Seleccionar lección</option>';
                response.lecciones.forEach(leccion => {
                    opciones += `<option value="${leccion.id}">${leccion.titulo}</option>`;
                });
                $('#vincular_leccion').html(opciones);
            } else {
                $('#vincular_leccion').html('<option value="">No hay lecciones disponibles</option>');
            }
        },
        error: function() {
            $('#vincular_leccion').html('<option value="">Error al cargar lecciones</option>');
        }
    });
}

// FUNCIÓN PARA CARGAR VINCULACIONES ACTUALES
function cargarVinculacionesActuales(recursoId) {
    $.ajax({
        url: 'modales/recursos/procesar_recursos.php',
        type: 'POST',
        data: { accion: 'obtener_vinculaciones', recurso_id: recursoId },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.vinculaciones.length > 0) {
                let html = '';
                
                response.vinculaciones.forEach(vinc => {
                    html += `<div class="vinculacion-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${vinc.tipo === 'CURSO' ? 'Curso' : 'Lección'}:</strong> 
                                ${vinc.nombre}
                                ${vinc.tipo === 'LECCION' ? `<br><small class="text-muted">Unidad: ${vinc.unidad_nombre}</small>` : ''}
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-desvincular" 
                                    onclick="desvincularRecurso(${vinc.id}, '${vinc.tipo}')">
                                <i class="ti ti-unlink"></i> Desvincular
                            </button>
                        </div>
                    </div>`;
                });
                
                $('#vinculaciones_actuales_container').html(html);
            } else {
                $('#vinculaciones_actuales_container').html(`
                    <div class="text-center text-muted py-3">
                        <i class="ti ti-unlink" style="font-size: 2rem;"></i>
                        <p class="mb-0 mt-2">Sin vinculaciones actuales</p>
                    </div>
                `);
            }
        }
    });
}

// FUNCIÓN PARA DESVINCULAR RECURSO
function desvincularRecurso(vinculacionId, tipo) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Deseas eliminar esta vinculación?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, desvincular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'modales/recursos/procesar_recursos.php',
                type: 'POST',
                data: { 
                    accion: 'desvincular', 
                    vinculacion_id: vinculacionId,
                    tipo: tipo
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarExito(response.message);
                        const recursoId = $('#vincular_recurso_id').val();
                        cargarVinculacionesActuales(recursoId);
                    } else {
                        mostrarError(response.message);
                    }
                },
                error: function() {
                    mostrarError('Error al desvincular recurso');
                }
            });
        }
    });
}

// FUNCIÓN PARA CARGAR INFO DEL RECURSO EN EL MODAL
function cargarInfoRecursoVinculacion(recurso) {
    const metadata = recurso.metadata ? JSON.parse(recurso.metadata) : {};
    
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
    
    let infoHTML = `
        <div class="d-flex align-items-center">
            <i class="ti ${icono}" style="font-size: 2.5rem; color: #667eea; margin-right: 15px;"></i>
            <div>
                <h6 class="mb-1">${recurso.titulo}</h6>
                <p class="mb-0">
                    <span class="badge bg-primary">${recurso.tipo}</span>
                    ${metadata.tamano_bytes ? `<span class="badge bg-secondary ms-2">${(metadata.tamano_bytes / 1048576).toFixed(2)} MB</span>` : ''}
                    ${metadata.duracion ? `<span class="badge bg-info ms-2">${metadata.duracion}</span>` : ''}
                </p>
            </div>
        </div>
    `;
    
    $('#vincular_recurso_info').html(infoHTML);
}

// FUNCIÓN PARA ABRIR MODAL DE VINCULACIÓN
function abrirModalVinculacion(recursoId) {
    mostrarCarga();
    
    $.ajax({
        url: 'modales/recursos/procesar_recursos.php',
        type: 'POST',
        data: { accion: 'obtener', id: recursoId },
        dataType: 'json',
        success: function(response) {
            ocultarCarga();
            
            if (response.success) {
                $('#vincular_recurso_id').val(recursoId);
                cargarInfoRecursoVinculacion(response.recurso);
                cargarVinculacionesActuales(recursoId);
                $('#modalVincularRecurso').modal('show');
            } else {
                mostrarError(response.message);
            }
        },
        error: function() {
            ocultarCarga();
            mostrarError('Error al cargar información del recurso');
        }
    });
}

function limpiarFormularioVinculacion() {
    $('#formVincularRecurso')[0].reset();
    $('.is-invalid, .campo-error').removeClass('is-invalid campo-error');
    $('.invalid-feedback').remove();
    $('#seccion_vincular_curso').hide();
    $('#seccion_vincular_leccion').hide();
    $('#vincular_unidad').html('<option value="">Primero selecciona un curso</option>');
    $('#vincular_leccion').html('<option value="">Primero selecciona una unidad</option>');
}
</script>