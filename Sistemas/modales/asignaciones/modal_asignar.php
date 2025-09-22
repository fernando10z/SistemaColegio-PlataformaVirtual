<?php
// modales/asignaciones/modal_asignar.php
?>
<!-- Modal Asignar Docente -->
<div class="modal fade" id="modalAsignarDocente" tabindex="-1" aria-labelledby="modalAsignarDocenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAsignarDocenteLabel">
                    <i class="ti ti-user-plus me-2"></i>
                    Nueva Asignación Docente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formAsignarDocente" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <!-- Información Básica de Asignación -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-school me-2"></i>
                                        Información de Asignación
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="asignar_docente_id" class="form-label">
                                                Docente <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="asignar_docente_id" name="docente_id" required>
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
                                            <label for="asignar_seccion_id" class="form-label">
                                                Sección <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="asignar_seccion_id" name="seccion_id" required>
                                                <option value="">Seleccionar sección</option>
                                                <!-- Se cargará dinámicamente con AJAX -->
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="asignar_area_id" class="form-label">
                                                Área Curricular <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="asignar_area_id" name="area_id" required>
                                                <option value="">Seleccionar área</option>
                                                <?php foreach ($areas as $area): ?>
                                                    <option value="<?= $area['id'] ?>">
                                                        <?= htmlspecialchars($area['nombre']) ?> (<?= htmlspecialchars($area['codigo']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="asignar_periodo_id" class="form-label">
                                                Período Académico <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="asignar_periodo_id" name="periodo_academico_id" required>
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
                                            <label for="asignar_horas_semanales" class="form-label">
                                                Horas Semanales <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control" id="asignar_horas_semanales" 
                                                   name="horas_semanales" min="1" max="30" value="2" required>
                                            <div class="form-text">Número de horas académicas por semana</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" id="asignar_es_tutor" 
                                                       name="es_tutor" value="1">
                                                <label class="form-check-label" for="asignar_es_tutor">
                                                    <strong>Es Tutor de la Sección</strong>
                                                </label>
                                                <div class="form-text">El docente será tutor de esta sección</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Horarios (Básico) -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-dark">
                                        <i class="ti ti-calendar-time me-2"></i>
                                        Horarios de Clase
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarHorario">
                                        <i class="ti ti-plus"></i> Agregar Horario
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="horariosContainer">
                                        <div class="alert alert-info">
                                            <i class="ti ti-info-circle me-2"></i>
                                            Agrega los horarios de clase para esta asignación. Puedes configurar horarios detallados después de crear la asignación.
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
                    <button type="submit" class="btn btn-primary" id="btnGuardarAsignacion">
                        <i class="ti ti-device-floppy me-2"></i>
                        Crear Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    let contadorHorarios = 0;

    // Cargar secciones al cambiar el nivel
    function cargarSecciones() {
        const selectSecciones = $('#asignar_seccion_id');
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
                        selectSecciones.append(`
                            <option value="${seccion.id}">
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

    // Agregar horario
    $('#btnAgregarHorario').on('click', function() {
        contadorHorarios++;
        const horarioHtml = `
            <div class="horario-item border rounded p-3 mb-2" data-index="${contadorHorarios}">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label class="form-label">Día</label>
                        <select class="form-select" name="horarios[${contadorHorarios}][dia]" required>
                            <option value="">Seleccionar</option>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hora Inicio</label>
                        <input type="time" class="form-control" name="horarios[${contadorHorarios}][hora_inicio]" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hora Fin</label>
                        <input type="time" class="form-control" name="horarios[${contadorHorarios}][hora_fin]" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-danger d-block w-100 btn-eliminar-horario">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#horariosContainer').append(horarioHtml);
    });

    // Eliminar horario
    $(document).on('click', '.btn-eliminar-horario', function() {
        $(this).closest('.horario-item').remove();
    });

    // Validar tutoría única
    $('#asignar_es_tutor').on('change', function() {
        if ($(this).is(':checked')) {
            const seccionId = $('#asignar_seccion_id').val();
            if (seccionId) {
                verificarTutorExistente(seccionId);
            }
        }
    });

    // Verificar tutor existente
    function verificarTutorExistente(seccionId) {
        $.ajax({
            url: 'modales/asignaciones/procesar_asignaciones.php',
            type: 'POST',
            data: { 
                accion: 'verificar_tutor',
                seccion_id: seccionId 
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
                            $('#asignar_es_tutor').prop('checked', false);
                        }
                    });
                }
            }
        });
    }

    // Envío del formulario
    $('#formAsignarDocente').on('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormularioAsignar()) {
            return false;
        }

        const formData = new FormData(this);
        formData.append('accion', 'crear');

        mostrarCarga();
        $('#btnGuardarAsignacion').prop('disabled', true);

        $.ajax({
            url: 'modales/asignaciones/procesar_asignaciones.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                ocultarCarga();
                $('#btnGuardarAsignacion').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        title: '¡Asignación Creada!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        $('#modalAsignarDocente').modal('hide');
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
                $('#btnGuardarAsignacion').prop('disabled', false);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Validar formulario
    function validarFormularioAsignar() {
        let isValid = true;
        
        // Validar campos requeridos
        const camposRequeridos = ['docente_id', 'seccion_id', 'area_id', 'horas_semanales'];
        camposRequeridos.forEach(function(campo) {
            const valor = $(`#asignar_${campo}`).val();
            if (!valor || valor.trim() === '') {
                mostrarErrorCampo(`#asignar_${campo}`, 'Este campo es requerido');
                isValid = false;
            }
        });

        // Validar horas semanales
        const horas = parseInt($('#asignar_horas_semanales').val());
        if (horas < 1 || horas > 30) {
            mostrarErrorCampo('#asignar_horas_semanales', 'Las horas deben estar entre 1 y 30');
            isValid = false;
        }

        return isValid;
    }

    function mostrarErrorCampo(campo, mensaje) {
        $(campo).addClass('is-invalid');
        if ($(campo).next('.invalid-feedback').length === 0) {
            $(campo).after(`<div class="invalid-feedback">${mensaje}</div>`);
        }
    }

    // Cargar secciones al abrir el modal
    $('#modalAsignarDocente').on('shown.bs.modal', function() {
        cargarSecciones();
    });

    // Limpiar formulario al cerrar modal
    $('#modalAsignarDocente').on('hidden.bs.modal', function() {
        $('#formAsignarDocente')[0].reset();
        $('#horariosContainer').empty().html(`
            <div class="alert alert-info">
                <i class="ti ti-info-circle me-2"></i>
                Agrega los horarios de clase para esta asignación. Puedes configurar horarios detallados después de crear la asignación.
            </div>
        `);
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        contadorHorarios = 0;
    });
});
</script>