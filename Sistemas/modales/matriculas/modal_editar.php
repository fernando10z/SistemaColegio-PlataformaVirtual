<?php
// modales/matriculas/modal_editar.php
?>
<!-- Modal Editar Matrícula -->
<div class="modal fade" id="modalEditarMatricula" tabindex="-1" aria-labelledby="modalEditarMatriculaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEditarMatriculaLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Matrícula
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarMatricula" method="POST">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Información del Estudiante (Solo lectura) -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-user me-2"></i>
                                        Información del Estudiante
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <img id="edit_estudiante_foto" src="../assets/images/profile/user-default.jpg" 
                                                 class="rounded-circle" width="60" height="60" alt="Foto">
                                        </div>
                                        <div class="col">
                                            <div class="fw-bold fs-5" id="edit_estudiante_nombre">-</div>
                                            <div class="text-muted" id="edit_estudiante_detalles">-</div>
                                        </div>
                                        <div class="col-auto">
                                            <span class="badge bg-primary fs-6" id="edit_estudiante_codigo">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de la Matrícula -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-school me-2"></i>
                                        Información de la Matrícula
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_codigo_matricula" class="form-label">
                                                Código Matrícula <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_codigo_matricula" 
                                                   name="codigo_matricula" readonly>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_seccion_id" class="form-label">
                                                Sección <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_seccion_id" name="seccion_id" required>
                                                <option value="">Seleccionar sección</option>
                                                <?php foreach ($secciones as $seccion): ?>
                                                    <option value="<?= $seccion['id'] ?>" 
                                                            data-capacidad="<?= $seccion['capacidad_maxima'] ?>"
                                                            data-ocupados="<?= $seccion['estudiantes_matriculados'] ?>"
                                                            data-disponibles="<?= $seccion['vacantes_disponibles'] ?>">
                                                        <?= htmlspecialchars($seccion['nivel_nombre'] . ' - ' . $seccion['grado'] . $seccion['seccion']) ?>
                                                        (<?= $seccion['vacantes_disponibles'] ?> vacantes)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_tipo_matricula" class="form-label">
                                                Tipo de Matrícula <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_tipo_matricula" name="tipo_matricula" required>
                                                <option value="NUEVO">Nuevo</option>
                                                <option value="CONTINUADOR">Continuador</option>
                                                <option value="TRASLADO">Traslado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_estado" class="form-label">
                                                Estado <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_estado" name="estado" required>
                                                <option value="MATRICULADO">Matriculado</option>
                                                <option value="TRASLADADO">Trasladado</option>
                                                <option value="RETIRADO">Retirado</option>
                                                <option value="RESERVADO">Reservado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_fecha_matricula" class="form-label">
                                                Fecha de Matrícula <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="edit_fecha_matricula" 
                                                   name="fecha_matricula" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="edit_periodo_academico_id" class="form-label">
                                                Período Académico <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="edit_periodo_academico_id" name="periodo_academico_id" required>
                                                <?php foreach ($periodos as $periodo): ?>
                                                    <option value="<?= $periodo['id'] ?>">
                                                        <?= htmlspecialchars($periodo['nombre']) ?> (<?= $periodo['anio'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datos Adicionales -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-file-text me-2"></i>
                                        Datos Adicionales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="edit_observaciones" class="form-label">Observaciones</label>
                                            <textarea class="form-control" id="edit_observaciones" name="observaciones" 
                                                      rows="3" placeholder="Observaciones adicionales sobre la matrícula..."></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="edit_documentos_completos" 
                                                       name="documentos_completos" value="1">
                                                <label class="form-check-label" for="edit_documentos_completos">
                                                    <strong>Documentos Completos</strong>
                                                </label>
                                                <div class="form-text">Documentación requerida está completa</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="edit_activo" 
                                                       name="activo" value="1" checked>
                                                <label class="form-check-label" for="edit_activo">
                                                    <strong>Matrícula Activa</strong>
                                                </label>
                                                <div class="form-text">Si está desactivada, no aparecerá en reportes</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Historial de Cambios -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-history me-2"></i>
                                        Información del Registro
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">Fecha de creación:</small>
                                            <div class="fw-bold" id="edit_fecha_creacion">-</div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Última actualización:</small>
                                            <div class="fw-bold" id="edit_fecha_actualizacion">-</div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3" id="historial-cambios-container" style="display: none;">
                                        <div class="alert alert-info">
                                            <h6 class="alert-heading">Historial de Cambios</h6>
                                            <div id="historial-cambios-lista">
                                                <!-- Se cargará dinámicamente -->
                                            </div>
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
                    <button type="button" class="btn btn-outline-secondary" id="btnVerHistorial">
                        <i class="ti ti-history me-2"></i>
                        Ver Historial
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnImprimirConstancia">
                        <i class="ti ti-printer me-2"></i>
                        Imprimir Constancia
                    </button>
                    <button type="submit" class="btn btn-info" id="btnActualizarMatricula">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Matrícula
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cargarDatosEdicionMatricula(matricula) {
    // Cargar datos básicos
    $('#edit_id').val(matricula.id);
    $('#edit_codigo_matricula').val(matricula.codigo_matricula);
    $('#edit_seccion_id').val(matricula.seccion_id);
    $('#edit_tipo_matricula').val(matricula.tipo_matricula);
    $('#edit_estado').val(matricula.estado);
    $('#edit_fecha_matricula').val(matricula.fecha_matricula);
    $('#edit_periodo_academico_id').val(matricula.periodo_academico_id);
    $('#edit_activo').prop('checked', matricula.activo == 1);
    
    // Cargar información del estudiante
    $('#edit_estudiante_nombre').text(matricula.estudiante_nombres + ' ' + matricula.estudiante_apellidos);
    $('#edit_estudiante_detalles').text('Documento: ' + matricula.documento_numero);
    $('#edit_estudiante_codigo').text(matricula.codigo_estudiante);
    
    if (matricula.foto_url) {
        $('#edit_estudiante_foto').attr('src', matricula.foto_url);
    }
    
    // Cargar datos adicionales
    const datosMatricula = matricula.datos_matricula ? JSON.parse(matricula.datos_matricula) : {};
    $('#edit_observaciones').val(datosMatricula.observaciones || '');
    $('#edit_documentos_completos').prop('checked', datosMatricula.documentos_completos || false);
    
    // Cargar fechas de registro
    $('#edit_fecha_creacion').text(formatearFecha(matricula.fecha_creacion, true));
    $('#edit_fecha_actualizacion').text(formatearFecha(matricula.fecha_actualizacion, true));
    
    // Configurar botones adicionales
    $('#btnVerHistorial').off('click').on('click', function() {
        verHistorialMatricula(matricula.id);
    });
    
    $('#btnImprimirConstancia').off('click').on('click', function() {
        imprimirConstancia(matricula.id);
    });
}

function verHistorialMatricula(id) {
    const container = $('#historial-cambios-container');
    if (container.is(':visible')) {
        container.slideUp();
        $('#btnVerHistorial i').removeClass('ti-eye-off').addClass('ti-history');
        return;
    }
    
    mostrarCarga();
    
    fetch('modales/matriculas/procesar_matriculas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=historial&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        ocultarCarga();
        
        if (data.success) {
            let historialHtml = '';
            
            if (data.historial && data.historial.length > 0) {
                data.historial.forEach(evento => {
                    historialHtml += `
                        <div class="border-start border-3 border-primary ps-3 mb-2">
                            <div class="fw-bold">${evento.accion}</div>
                            <div class="text-muted small">${formatearFecha(evento.fecha, true)}</div>
                            ${evento.detalles ? `<div class="small">${evento.detalles}</div>` : ''}
                        </div>
                    `;
                });
            } else {
                historialHtml = '<div class="text-muted">No hay cambios registrados</div>';
            }
            
            $('#historial-cambios-lista').html(historialHtml);
            container.slideDown();
            $('#btnVerHistorial i').removeClass('ti-history').addClass('ti-eye-off');
        } else {
            mostrarError(data.message);
        }
    })
    .catch(error => {
        ocultarCarga();
        mostrarError('Error al cargar historial');
    });
}

$(document).ready(function() {
    // Envío del formulario
    $('#formEditarMatricula').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioEditarMatricula()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'actualizar');
        
        mostrarCarga();
        $('#btnActualizarMatricula').prop('disabled', true);

        $.ajax({
            url: 'modales/matriculas/procesar_matriculas.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarMatricula').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Matrícula Actualizada!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarMatricula').modal('hide');
                        location.reload();
                    });
                } else {
                    mostrarError(response.message);
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnActualizarMatricula').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalEditarMatricula').on('hidden.bs.modal', function() {
        $('#formEditarMatricula')[0].reset();
        $('#historial-cambios-container').hide();
        $('#btnVerHistorial i').removeClass('ti-eye-off').addClass('ti-history');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });
});

function validarFormularioEditarMatricula() {
    let isValid = true;
    
    // Limpiar errores previos
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Validar campos requeridos
    const camposRequeridos = [
        '#edit_seccion_id', 
        '#edit_tipo_matricula', 
        '#edit_estado', 
        '#edit_fecha_matricula', 
        '#edit_periodo_academico_id'
    ];
    
    camposRequeridos.forEach(function(campo) {
        if (!$(campo).val()) {
            $(campo).addClass('is-invalid');
            $(campo).after('<div class="invalid-feedback">Este campo es requerido</div>');
            isValid = false;
        }
    });
    
    // Validar fecha de matrícula
    const fechaMatricula = new Date($('#edit_fecha_matricula').val());
    const hoy = new Date();
    const diferenciaDias = (fechaMatricula - hoy) / (1000 * 60 * 60 * 24);
    
    if (diferenciaDias > 30) {
        $('#edit_fecha_matricula').addClass('is-invalid');
        $('#edit_fecha_matricula').after('<div class="invalid-feedback">La fecha de matrícula no puede ser más de 30 días en el futuro</div>');
        isValid = false;
    }
    
    return isValid;
}

function formatearFecha(fecha, incluirHora = false) {
    if (!fecha) return '-';
    
    const fechaObj = new Date(fecha);
    const opciones = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    };
    
    if (incluirHora) {
        opciones.hour = '2-digit';
        opciones.minute = '2-digit';
    }
    
    return fechaObj.toLocaleDateString('es-PE', opciones);
}
</script>