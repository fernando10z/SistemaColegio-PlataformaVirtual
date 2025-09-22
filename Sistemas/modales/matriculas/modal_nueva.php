<?php
// modales/matriculas/modal_nueva.php
// Obtener estudiantes sin matrícula activa
try {
    $stmt_estudiantes = $conexion->prepare("
        SELECT e.*, 
               CASE WHEN m.id IS NULL THEN 1 ELSE 0 END as disponible
        FROM estudiantes e
        LEFT JOIN matriculas m ON e.id = m.estudiante_id 
                               AND m.estado = 'MATRICULADO' 
                               AND m.activo = 1
                               AND m.periodo_academico_id = ?
        WHERE e.activo = 1
        ORDER BY e.apellidos ASC, e.nombres ASC
    ");
    $stmt_estudiantes->execute([$periodo_actual['id'] ?? 0]);
    $estudiantes_disponibles = $stmt_estudiantes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $estudiantes_disponibles = [];
}
?>

<!-- Modal Nueva Matrícula -->
<div class="modal fade" id="modalNuevaMatricula" tabindex="-1" aria-labelledby="modalNuevaMatriculaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalNuevaMatriculaLabel">
                    <i class="ti ti-user-plus me-2"></i>
                    Nueva Matrícula
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formNuevaMatricula" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <!-- Información del Estudiante -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-user me-2"></i>
                                        Información del Estudiante
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label for="add_estudiante_id" class="form-label">
                                                Estudiante <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_estudiante_id" name="estudiante_id" required>
                                                <option value="">Seleccionar estudiante</option>
                                                <?php foreach ($estudiantes_disponibles as $estudiante): ?>
                                                    <option value="<?= $estudiante['id'] ?>" 
                                                            data-nombres="<?= htmlspecialchars($estudiante['nombres']) ?>"
                                                            data-apellidos="<?= htmlspecialchars($estudiante['apellidos']) ?>"
                                                            data-documento="<?= htmlspecialchars($estudiante['documento_numero']) ?>"
                                                            data-codigo="<?= htmlspecialchars($estudiante['codigo_estudiante']) ?>"
                                                            data-disponible="<?= $estudiante['disponible'] ?>"
                                                            <?= !$estudiante['disponible'] ? 'disabled' : '' ?>>
                                                        <?= htmlspecialchars($estudiante['apellidos'] . ', ' . $estudiante['nombres']) ?>
                                                        (<?= htmlspecialchars($estudiante['codigo_estudiante']) ?>)
                                                        <?= !$estudiante['disponible'] ? ' - YA MATRICULADO' : '' ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Solo se muestran estudiantes sin matrícula activa en el período actual</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_codigo_matricula" class="form-label">
                                                Código Matrícula <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="add_codigo_matricula" 
                                                       name="codigo_matricula" placeholder="Generado automáticamente" readonly>
                                                <button type="button" class="btn btn-outline-secondary" id="btnGenerarCodigo">
                                                    <i class="ti ti-refresh"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Vista previa del estudiante seleccionado -->
                                    <div id="preview-estudiante" class="alert alert-info" style="display: none;">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div class="avatar-lg">
                                                    <img id="preview-foto" src="../assets/images/profile/user-default.jpg" 
                                                         class="rounded-circle" width="60" height="60" alt="Foto">
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="fw-bold" id="preview-nombre">-</div>
                                                <div class="text-muted" id="preview-detalles">-</div>
                                            </div>
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
                                            <label for="add_seccion_id" class="form-label">
                                                Sección <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_seccion_id" name="seccion_id" required>
                                                <option value="">Seleccionar sección</option>
                                                <?php foreach ($secciones as $seccion): ?>
                                                    <option value="<?= $seccion['id'] ?>" 
                                                            data-capacidad="<?= $seccion['capacidad_maxima'] ?>"
                                                            data-ocupados="<?= $seccion['estudiantes_matriculados'] ?>"
                                                            data-disponibles="<?= $seccion['vacantes_disponibles'] ?>"
                                                            <?= $seccion['vacantes_disponibles'] <= 0 ? 'disabled' : '' ?>>
                                                        <?= htmlspecialchars($seccion['nivel_nombre'] . ' - ' . $seccion['grado'] . $seccion['seccion']) ?>
                                                        (<?= $seccion['vacantes_disponibles'] ?> vacantes)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_tipo_matricula" class="form-label">
                                                Tipo de Matrícula <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_tipo_matricula" name="tipo_matricula" required>
                                                <option value="">Seleccionar tipo</option>
                                                <option value="NUEVO">Nuevo</option>
                                                <option value="CONTINUADOR">Continuador</option>
                                                <option value="TRASLADO">Traslado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="add_fecha_matricula" class="form-label">
                                                Fecha de Matrícula <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="add_fecha_matricula" 
                                                   name="fecha_matricula" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="add_periodo_academico_id" class="form-label">
                                                Período Académico <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="add_periodo_academico_id" name="periodo_academico_id" required>
                                                <?php foreach ($periodos as $periodo): ?>
                                                    <option value="<?= $periodo['id'] ?>" 
                                                            <?= $periodo_actual && $periodo['id'] == $periodo_actual['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($periodo['nombre']) ?> (<?= $periodo['anio'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Vista previa de la sección -->
                                    <div id="preview-seccion" class="alert alert-warning" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="fw-bold" id="preview-seccion-nombre">-</div>
                                                <div class="text-muted" id="preview-seccion-detalles">-</div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="fw-bold text-primary" id="preview-vacantes">-</div>
                                                <div class="progress mt-1" style="height: 8px;">
                                                    <div class="progress-bar" id="preview-ocupacion-bar" style="width: 0%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documentos y Observaciones -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-file-text me-2"></i>
                                        Documentos y Observaciones
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="add_observaciones" class="form-label">Observaciones</label>
                                            <textarea class="form-control" id="add_observaciones" name="observaciones" 
                                                      rows="3" placeholder="Observaciones adicionales sobre la matrícula..."></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="add_documentos_completos" 
                                                       name="documentos_completos" value="1" checked>
                                                <label class="form-check-label" for="add_documentos_completos">
                                                    <strong>Documentos Completos</strong>
                                                </label>
                                                <div class="form-text">Confirma que toda la documentación requerida está completa</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="add_generar_constancia" 
                                                       name="generar_constancia" value="1">
                                                <label class="form-check-label" for="add_generar_constancia">
                                                    <strong>Generar Constancia</strong>
                                                </label>
                                                <div class="form-text">Generar automáticamente constancia de matrícula</div>
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarMatricula">
                        <i class="ti ti-device-floppy me-2"></i>
                        Registrar Matrícula
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Generar código automático al cargar
    generarCodigoAutomatico();
    
    // Generar código manualmente
    $('#btnGenerarCodigo').on('click', function() {
        generarCodigoAutomatico();
    });

    // Preview del estudiante seleccionado
    $('#add_estudiante_id').on('change', function() {
        const option = $(this).find('option:selected');
        if (option.val()) {
            $('#preview-estudiante').show();
            $('#preview-nombre').text(option.data('nombres') + ' ' + option.data('apellidos'));
            $('#preview-detalles').text('Código: ' + option.data('codigo') + ' | Documento: ' + option.data('documento'));
            
            // Actualizar código de matrícula según el estudiante
            generarCodigoAutomatico();
        } else {
            $('#preview-estudiante').hide();
        }
    });

    // Preview de la sección seleccionada
    $('#add_seccion_id').on('change', function() {
        const option = $(this).find('option:selected');
        if (option.val()) {
            const capacidad = parseInt(option.data('capacidad'));
            const ocupados = parseInt(option.data('ocupados'));
            const disponibles = parseInt(option.data('disponibles'));
            const porcentaje = Math.round((ocupados / capacidad) * 100);
            
            $('#preview-seccion').show();
            $('#preview-seccion-nombre').text(option.text().split('(')[0].trim());
            $('#preview-seccion-detalles').text(`Aula asignada | Capacidad: ${capacidad} estudiantes`);
            $('#preview-vacantes').text(`${disponibles} vacantes disponibles`);
            
            // Actualizar barra de progreso
            const progressClass = porcentaje >= 90 ? 'bg-danger' : porcentaje >= 75 ? 'bg-warning' : 'bg-success';
            $('#preview-ocupacion-bar').removeClass('bg-success bg-warning bg-danger').addClass(progressClass);
            $('#preview-ocupacion-bar').css('width', porcentaje + '%');
            
            if (disponibles <= 0) {
                $('#preview-seccion').removeClass('alert-warning').addClass('alert-danger');
                $('#preview-vacantes').text('SECCIÓN COMPLETA');
            } else if (disponibles <= 3) {
                $('#preview-seccion').removeClass('alert-danger').addClass('alert-warning');
            } else {
                $('#preview-seccion').removeClass('alert-danger alert-warning').addClass('alert-warning');
            }
        } else {
            $('#preview-seccion').hide();
        }
    });

    // Envío del formulario
    $('#formNuevaMatricula').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioNuevaMatricula()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');
        
        mostrarCarga();
        $('#btnGuardarMatricula').prop('disabled', true);

        $.ajax({
            url: 'modales/matriculas/procesar_matriculas.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarMatricula').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Matrícula Registrada!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalNuevaMatricula').modal('hide');
                        location.reload();
                    });
                } else {
                    mostrarError(response.message);
                }
            },
            error: function() {
                ocultarCarga();
                $('#btnGuardarMatricula').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#modalNuevaMatricula').on('hidden.bs.modal', function() {
        $('#formNuevaMatricula')[0].reset();
        $('#preview-estudiante, #preview-seccion').hide();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        generarCodigoAutomatico();
    });
});

function generarCodigoAutomatico() {
    const año = new Date().getFullYear();
    const tipo = $('#add_tipo_matricula').val() || 'NUEVO';
    const prefijo = tipo === 'NUEVO' ? 'MAT' : tipo === 'CONTINUADOR' ? 'CON' : 'TRA';
    const timestamp = Date.now().toString().slice(-4);
    const random = Math.floor(Math.random() * 100).toString().padStart(2, '0');
    
    const codigo = `${prefijo}${año}${timestamp}${random}`;
    $('#add_codigo_matricula').val(codigo);
}

function validarFormularioNuevaMatricula() {
    let isValid = true;
    
    // Limpiar errores previos
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Validar campos requeridos
    const camposRequeridos = [
        '#add_estudiante_id', 
        '#add_seccion_id', 
        '#add_tipo_matricula', 
        '#add_fecha_matricula', 
        '#add_periodo_academico_id'
    ];
    
    camposRequeridos.forEach(function(campo) {
        if (!$(campo).val()) {
            $(campo).addClass('is-invalid');
            $(campo).after('<div class="invalid-feedback">Este campo es requerido</div>');
            isValid = false;
        }
    });
    
    // Validar que el estudiante esté disponible
    const estudianteOption = $('#add_estudiante_id option:selected');
    if (estudianteOption.data('disponible') == 0) {
        $('#add_estudiante_id').addClass('is-invalid');
        $('#add_estudiante_id').after('<div class="invalid-feedback">Este estudiante ya tiene una matrícula activa</div>');
        isValid = false;
    }
    
    // Validar que la sección tenga vacantes
    const seccionOption = $('#add_seccion_id option:selected');
    if (seccionOption.data('disponibles') <= 0) {
        $('#add_seccion_id').addClass('is-invalid');
        $('#add_seccion_id').after('<div class="invalid-feedback">Esta sección no tiene vacantes disponibles</div>');
        isValid = false;
    }
    
    // Validar fecha de matrícula
    const fechaMatricula = new Date($('#add_fecha_matricula').val());
    const hoy = new Date();
    const diferenciaDias = (fechaMatricula - hoy) / (1000 * 60 * 60 * 24);
    
    if (diferenciaDias > 30) {
        $('#add_fecha_matricula').addClass('is-invalid');
        $('#add_fecha_matricula').after('<div class="invalid-feedback">La fecha de matrícula no puede ser más de 30 días en el futuro</div>');
        isValid = false;
    }
    
    return isValid;
}
</script>