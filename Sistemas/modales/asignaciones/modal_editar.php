<?php
// modales/asignaciones/modal_editar.php
?>
<!-- Modal Editar Asignación -->
<div class="modal fade" id="modalEditarAsignacion" tabindex="-1" aria-labelledby="modalEditarAsignacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="modalEditarAsignacionLabel">
                    <i class="ti ti-edit me-2"></i>
                    Editar Asignación Docente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarAsignacion" method="POST">
                <input type="hidden" id="editar_asignacion_id" name="asignacion_id">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Actual -->
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>
                                <strong>Docente Actual:</strong> <span id="info_docente_actual"></span><br>
                                <strong>Sección:</strong> <span id="info_seccion_actual"></span><br>
                                <strong>Área:</strong> <span id="info_area_actual"></span>
                            </div>
                        </div>

                        <!-- Información Básica de Asignación -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-school me-2"></i>
                                        Modificar Asignación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="editar_docente_id" class="form-label">
                                                Docente <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="editar_docente_id" name="docente_id" required>
                                                <option value="">Seleccionar docente</option>
                                                <?php foreach ($docentes as $docente): ?>
                                                    <option value="<?= $docente['id'] ?>">
                                                        <?= htmlspecialchars($docente['apellidos'] . ', ' . $docente['nombres']) ?>
                                                        (<?= htmlspecialchars($docente['codigo_docente']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editar_seccion_id" class="form-label">
                                                Sección <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="editar_seccion_id" name="seccion_id" required>
                                                <option value="">Seleccionar sección</option>
                                                <!-- Se cargará dinámicamente -->
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editar_area_id" class="form-label">
                                                Área Curricular <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="editar_area_id" name="area_id" required>
                                                <option value="">Seleccionar área</option>
                                                <?php foreach ($areas as $area): ?>
                                                    <option value="<?= $area['id'] ?>">
                                                        <?= htmlspecialchars($area['nombre']) ?> (<?= htmlspecialchars($area['codigo']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editar_periodo_id" class="form-label">
                                                Período Académico <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="editar_periodo_id" name="periodo_academico_id" required>
                                                <?php if ($periodo_actual): ?>
                                                    <option value="<?= $periodo_actual['id'] ?>" selected>
                                                        <?= htmlspecialchars($periodo_actual['nombre']) ?> (<?= $periodo_actual['anio'] ?>)
                                                    </option>
                                                <?php else: ?>
                                                    <option value="">No hay período académico activo</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración Académica -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-clock me-2"></i>
                                        Configuración Académica
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="editar_horas_semanales" class="form-label">
                                                Horas Semanales <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="editar_horas_semanales" 
                                                   name="horas_semanales" min="1" max="30" required>
                                            <div class="form-text">Número de horas académicas por semana</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" id="editar_es_tutor" 
                                                       name="es_tutor" value="1">
                                                <label class="form-check-label" for="editar_es_tutor">
                                                    <strong>Es Tutor de la Sección</strong>
                                                </label>
                                                <div class="form-text">El docente será tutor de esta sección</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-toggle-right me-2"></i>
                                        Estado de la Asignación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="editar_activo" 
                                               name="activo" value="1" checked>
                                        <label class="form-check-label" for="editar_activo">
                                            <strong>Asignación Activa</strong>
                                        </label>
                                        <div class="form-text">Desactivar temporalmente esta asignación sin eliminarla</div>
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
                    <button type="button" class="btn btn-info" id="btnEditarHorarios">
                        <i class="ti ti-calendar-time me-2"></i>
                        Editar Horarios
                    </button>
                    <button type="submit" class="btn btn-warning" id="btnActualizarAsignacion">
                        <i class="ti ti-device-floppy me-2"></i>
                        Actualizar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Función para cargar datos de edición (llamada desde el PHP principal)
function cargarDatosEdicionAsignacion(asignacion) {
    // Llenar campos básicos
    $('#editar_asignacion_id').val(asignacion.id);
    $('#editar_docente_id').val(asignacion.docente_id);
    $('#editar_area_id').val(asignacion.area_id);
    $('#editar_periodo_id').val(asignacion.periodo_academico_id);
    $('#editar_horas_semanales').val(asignacion.horas_semanales);
    $('#editar_es_tutor').prop('checked', asignacion.es_tutor == 1);
    $('#editar_activo').prop('checked', asignacion.activo == 1);

    // Información actual
    $('#info_docente_actual').text(`${asignacion.docente_nombres} ${asignacion.docente_apellidos}`);
    $('#info_seccion_actual').text(`${asignacion.nivel_nombre} - ${asignacion.grado} "${asignacion.seccion}"`);
    $('#info_area_actual').text(`${asignacion.area_nombre} (${asignacion.area_codigo})`);

    // Cargar secciones y seleccionar la actual
    cargarSeccionesEditar(asignacion.seccion_id);
}

function cargarSeccionesEditar(seccionSeleccionada = null) {
    const selectSecciones = $('#editar_seccion_id');
    selectSecciones.html('<option value="">Cargando secciones...</option>');
    
    $.ajax({
        url: 'modales/asignaciones/procesar_asignaciones.php',
        type: 'POST',
        data: { accion: 'obtener_secciones' },
        dataType: 'json',
        success: function(response) {
            selectSecciones.html('<option value="">Seleccionar sección</option>');
            if (response.success && response.secciones) {
                response.secciones.forEach(function(seccion) {
                    const selected = seccionSeleccionada == seccion.id ? 'selected' : '';
                    selectSecciones.append(`
                        <option value="${seccion.id}" ${selected}>
                            ${seccion.nivel_nombre} - ${seccion.grado} "${seccion.seccion}" 
                            (${seccion.aula_asignada || 'Sin aula'})
                        </option>
                    `);
                });
            }
        },
        error: function() {
            selectSecciones.html('<option value="">Error al cargar secciones</option>');
        }
    });
}

$(document).ready(function() {
    // Botón editar horarios
    $('#btnEditarHorarios').on('click', function() {
        const asignacionId = $('#editar_asignacion_id').val();
        if (asignacionId) {
            $('#modalEditarAsignacion').modal('hide');
            // Abrir modal de horarios
            cargarModalHorarios(asignacionId);
        }
    });

    // Validar tutoría única en edición
    $('#editar_es_tutor').on('change', function() {
        if ($(this).is(':checked')) {
            const seccionId = $('#editar_seccion_id').val();
            const asignacionId = $('#editar_asignacion_id').val();
            if (seccionId) {
                verificarTutorExistenteEditar(seccionId, asignacionId);
            }
        }
    });

    function verificarTutorExistenteEditar(seccionId, asignacionId) {
        $.ajax({
            url: 'modales/asignaciones/procesar_asignaciones.php',
            type: 'POST',
            data: { 
                accion: 'verificar_tutor',
                seccion_id: seccionId,
                excluir_asignacion: asignacionId
            },
            dataType: 'json',
            success: function(response) {
                if (response.tiene_tutor) {
                    Swal.fire({
                        title: 'Advertencia',
                        text: `Esta sección ya tiene un tutor asignado: ${response.tutor_nombre}. ¿Deseas continuar? Se removerá la tutoría actual.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#fd7e14',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (!result.isConfirmed) {
                            $('#editar_es_tutor').prop('checked', false);
                        }
                    });
                }
            }
        });
    }

    // Envío del formulario de edición
    $('#formEditarAsignacion').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioEditar()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'actualizar');

        mostrarCarga();
        $('#btnActualizarAsignacion').prop('disabled', true);

        $.ajax({
            url: 'modales/asignaciones/procesar_asignaciones.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnActualizarAsignacion').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Asignación Actualizada!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalEditarAsignacion').modal('hide');
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
                $('#btnActualizarAsignacion').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    function validarFormularioEditar() {
        let isValid = true;
        
        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        // Validar campos requeridos
        const camposRequeridos = ['docente_id', 'seccion_id', 'area_id', 'horas_semanales'];
        camposRequeridos.forEach(function(campo) {
            const valor = $(`#editar_${campo}`).val();
            if (!valor || valor.trim() === '') {
                mostrarErrorCampoEditar(`#editar_${campo}`, 'Este campo es requerido');
                isValid = false;
            }
        });

        // Validar horas semanales
        const horas = parseInt($('#editar_horas_semanales').val());
        if (horas < 1 || horas > 30) {
            mostrarErrorCampoEditar('#editar_horas_semanales', 'Las horas deben estar entre 1 y 30');
            isValid = false;
        }

        return isValid;
    }

    function mostrarErrorCampoEditar(campo, mensaje) {
        $(campo).addClass('is-invalid');
        if ($(campo).next('.invalid-feedback').length === 0) {
            $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
        }
    }

    // Limpiar formulario al cerrar modal
    $('#modalEditarAsignacion').on('hidden.bs.modal', function() {
        $('#formEditarAsignacion')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });
});
</script>